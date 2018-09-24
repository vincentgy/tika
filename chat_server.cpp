#include <websocketpp/config/asio_no_tls.hpp>

#include <websocketpp/server.hpp>

#include <iostream>
#include <set>
#include <map>
#include <stdint.h>
#include <mysql.h>
#include <string>
/*#include <boost/thread.hpp>
#include <boost/thread/mutex.hpp>
#include <boost/thread/condition_variable.hpp>*/
#include <websocketpp/common/thread.hpp>

#define SERVER "localhost"
#define USER "root"
#define PASSWORD "r00t"
#define DATABASE "tikadb"


typedef websocketpp::server<websocketpp::config::asio> server;

using websocketpp::connection_hdl;
using websocketpp::lib::placeholders::_1;
using websocketpp::lib::placeholders::_2;
using websocketpp::lib::bind;

using websocketpp::lib::thread;
using websocketpp::lib::mutex;
using websocketpp::lib::lock_guard;
using websocketpp::lib::unique_lock;
using websocketpp::lib::condition_variable;

/* on_open insert connection_hdl into channel
 * on_close remove connection_hdl from channel
 * on_message queue send to all channels
 */

enum action_type {
    SUBSCRIBE,
    UNSUBSCRIBE,
    MESSAGE
};

enum OPCODE {
    CLIENTID = 1,
    CHATLIST = 2,
    NEWROOM = 3,
    JOIN   =4,
    HIST   =5,
    NEWMSG = 6,
    OLDMSG = 7,
    LASTSEEN = 8
};

struct command
{
    uint32_t opcode;
    uint32_t chatId;
    uint32_t userId;
    uint64_t messageId;
    uint32_t count;
    std::string token;
    std::string message;
    std::vector<uint32_t> users;
    std::vector<uint32_t> chatList;
};

struct action {
    action(action_type t, connection_hdl h) : type(t), hdl(h) {}
    action(action_type t, connection_hdl h, server::message_ptr m)
      : type(t), hdl(h), msg(m) {}

    action_type type;
    websocketpp::connection_hdl hdl;
    server::message_ptr msg;
};


std::string pack32le (uint32_t x) {
    std::string r;

    for (int i = 4; i--;) {
        r += ((char)(x & 255));
        x >>= 8;
    }

    return r;
}

uint32_t unpack32le(const std::string& x) {
    uint32_t r = 0;

    for (int i = 4; i--;) {
        r = ((r << 8) >> 0) + (uint32_t)(x[i]);
    }

    return r;
}

std::string pack16le (uint16_t x) {
    std::string r;

    for (int i = 2; i--;) {
        r += ((char)(x & 255));
        x >>= 8;
    }

    return r;
}

uint32_t unpack16le(const std::string& x) {
    uint32_t r = 0;

    for (int i = 2; i--;) {
        r = ((r << 8) >> 0) + (uint32_t)(x[i]);
    }

    return r;
}

void parse_cmd(const std::string& cmdq, command& r) {
    uint32_t opcode = (uint32_t)cmdq[0];
    int len = 0;
    r.opcode = opcode;
    std::cout<< "opcode:"<<r.opcode<<std::endl;
    for (int i =0;i<cmdq.length();i++) {
        std::cout<<std::hex<<(uint32_t)cmdq[i];
    }
    switch (opcode) {
        case OPCODE::CLIENTID:
            len = unpack16le(cmdq.substr(1, 2));
            std::string token = cmdq.substr(3, len);
            r.token = token;
        break;
        case OPCODE::JOIN:
            std::cout<<"parse JOIN"<<std::endl;
            r.chatId = unpack32le(cmdq.substr(1, 4));
            r.userId = unpack32le(cmdq.substr(5, 4));
        break;
        case OPCODE::NEWMSG:
        case OPCODE::OLDMSG:
            r.chatId = unpack32le(cmdq.substr(1, 4));
            r.userId = unpack32le(cmdq.substr(5, 4));
            len = unpack16le(cmdq.substr(9, 2));
            r.message = cmdq.substr(11, len);
        break;
    }
}

std::string assemble_cmd(const command& cmd) {
    std::string buf;
    buf += ((char)cmd.opcode);
    switch(cmd.opcode) {
        case OPCODE::JOIN:
            buf += pack32le(cmd.chatId);
            buf += pack32le(cmd.userId);
        break;
        case OPCODE::NEWMSG:
        case OPCODE::OLDMSG:
            buf += pack32le(cmd.chatId);
            buf += pack32le(cmd.userId);
            buf += pack16le(cmd.message.length());
            buf += cmd.message;
        break;
        case OPCODE::CHATLIST:
            buf += pack16le(cmd.chatList.size());
            for(int i = 0; i < cmd.chatList.size(); i++) {
                buf += pack32le(cmd.chatList[i]);
            }
        break;
    }
    return buf;
}
class broadcast_server {
public:
    broadcast_server() {
        // Initialize Asio Transport
        m_server.init_asio();

        // Register handler callbacks
        m_server.set_open_handler(bind(&broadcast_server::on_open,this,::_1));
        m_server.set_close_handler(bind(&broadcast_server::on_close,this,::_1));
        m_server.set_message_handler(bind(&broadcast_server::on_message,this,::_1,::_2));

        m_connect = mysql_init(NULL);
        if (!m_connect)
        {
            std::cout << "Mysql Initialization Failed" << std::endl;
        }

        m_connect = mysql_real_connect(m_connect, SERVER, USER, PASSWORD, DATABASE, 0, NULL, 0);

        if (m_connect)
        {
            std::cout << "Connection Succeeded\n" << std::endl;
        }
        else
        {
            std::cout << "Connection Failed\n" << std::endl;
        }
    }
    ~broadcast_server() {
        if (m_connect) {
            /* close the connection */
            mysql_close(m_connect);
        }
    }
    void run(uint16_t port) {
        // listen on specified port
        m_server.listen(port);

        // Start the server accept loop
        m_server.start_accept();

        // Start the ASIO io_service run loop
        try {
            m_server.run();
        } catch (const std::exception & e) {
            std::cout << e.what() << std::endl;
        }
    }

    void on_open(connection_hdl hdl) {
        {
            lock_guard<mutex> guard(m_action_lock);
            //std::cout << "on_open" << std::endl;
            m_actions.push(action(SUBSCRIBE,hdl));
        }
        m_action_cond.notify_one();
    }

    void on_close(connection_hdl hdl) {
        {
            lock_guard<mutex> guard(m_action_lock);
            //std::cout << "on_close" << std::endl;
            m_actions.push(action(UNSUBSCRIBE,hdl));
        }
        m_action_cond.notify_one();
    }

    void on_message(connection_hdl hdl, server::message_ptr msg) {
        // queue message up for sending by processing thread
        {
            lock_guard<mutex> guard(m_action_lock);
            //std::cout << "on_message" << std::endl;
            m_actions.push(action(MESSAGE,hdl,msg));
        }
        m_action_cond.notify_one();
    }

    void process_messages() {
        while(1) {
            unique_lock<mutex> lock(m_action_lock);

            while(m_actions.empty()) {
                m_action_cond.wait(lock);
            }

            action a = m_actions.front();
            m_actions.pop();

            lock.unlock();

            if (a.type == SUBSCRIBE) {
                lock_guard<mutex> guard(m_connection_lock);
                m_connections.insert(a.hdl);
            } else if (a.type == UNSUBSCRIBE) {
                std::cout<<"UNSUBSCRIBE"<<std::endl;
                lock_guard<mutex> guard(m_connection_lock);
                m_connections.erase(a.hdl);
            } else if (a.type == MESSAGE) {
                lock_guard<mutex> guard(m_connection_lock);
                command cmd;
                std::string response_str;
                parse_cmd(a.msg->get_payload(), cmd);
                std::cout<< "cmd opCode:" << cmd.opcode<<std::endl;

                if (OPCODE::CLIENTID == cmd.opcode) {
                        uint32_t user_id = getuseridbytoken(cmd.token);
                        std::cout<<"CLIENTID:"<<user_id<<','<<std::endl;
                        cmd.opcode = OPCODE::CHATLIST;
                        cmd.chatList = getchatlist(user_id);
                        response_str = assemble_cmd(cmd);
                        m_server.send(a.hdl, response_str,  a.msg->get_opcode());
                        for (int cIndex = 0; cIndex < cmd.chatList.size(); cIndex++) {
                            std::vector<uint32_t> userList = getparticipants(cmd.chatList[cIndex]);
                            for (int index = 0; index < userList.size();index++) {
                                std::cout<< 'JOINED:'<<userList[index]<<std::endl;
                                std::string str;
                                str += ((char)OPCODE::JOIN);
                                str += pack32le(cmd.chatList[cIndex]);
                                str += pack32le(userList[index]);
                                m_server.send(a.hdl, str,  a.msg->get_opcode());
                            }
                        }
                    }
                    else if (OPCODE::JOIN == cmd.opcode) {
                        std::cout<<"received JOIN"<<std::endl;
                        std::vector<uint32_t> userList = getparticipants(cmd.chatId);
                        for (int index = 0; index < userList.size();index++) {
                            std::cout<< 'JOINED:'<<userList[index]<<std::endl;
                            std::string str;
                            str += ((char)cmd.opcode);
                            str += pack32le(cmd.chatId);
                            str += pack32le(userList[index]);
                            m_server.send(a.hdl, str,  a.msg->get_opcode());
                        }
                    }
                    else if(OPCODE::NEWMSG == cmd.opcode) {
                        std::cout<<"NEWMSG:"<<cmd.chatId<<','<<cmd.userId<<','<<cmd.message<<std::endl;
                        response_str = assemble_cmd(cmd);
                    }

                con_list::iterator it;
                for (it = m_connections.begin(); it != m_connections.end(); ++it) {
                    m_server.send(*it, response_str,  a.msg->get_opcode());

                }
            } else {
                // undefined.
            }
        }
    }
protected:
    std::vector<uint32_t> getparticipants(uint32_t chat_id) {
        MYSQL_RES *result;
        MYSQL_ROW row;
        int state;
        std::vector<uint32_t> userList;
        std::string sql = std::string("SELECT user_id FROM chat_users WHERE chat_id = ") + std::to_string(chat_id);
        std::cout<<"sql:"<<sql<<std::endl;
        if (m_connect) {
            state = mysql_query(m_connect, sql.c_str());
            if( state != 0 ) {
                printf(mysql_error(m_connect));
                return userList;
            }
            /* must call mysql_store_result() before we can issue any
             * other query calls
             */  
            result = mysql_store_result(m_connect);
            printf("Rows: %d\n", mysql_num_rows(result));
            /* process each row in the result set */
            while( ( row = mysql_fetch_row(result)) != NULL ) {
                printf("id: %s, val: %s\n", 
                       (row[0] ? row[0] : "NULL"), 
                       (row[1] ? row[1] : "NULL"));
                userList.push_back(atoi(row[0]));
            }
            /* free the result set */
            mysql_free_result(result);
            printf("Done.\n");
        }
        return userList;
    }

    std::vector<uint32_t> getchatlist(uint32_t user_id) {
        MYSQL_RES *result;
        MYSQL_ROW row;
        int state;
        std::vector<uint32_t> chatList;
        std::string sql = std::string("SELECT DISTINCT(chat_id) FROM chat_users WHERE user_id = ") + std::to_string(user_id);
        std::cout<<"sql:"<<sql<<std::endl;
        if (m_connect) {
            state = mysql_query(m_connect, sql.c_str());
            if( state != 0 ) {
                printf(mysql_error(m_connect));
                return chatList;
            }
            /* must call mysql_store_result() before we can issue any
             * other query calls
             */  
            result = mysql_store_result(m_connect);
            printf("Rows: %d\n", mysql_num_rows(result));
            /* process each row in the result set */
            while( ( row = mysql_fetch_row(result)) != NULL ) {
                printf("id: %s, val: %s\n", 
                       (row[0] ? row[0] : "NULL"), 
                       (row[1] ? row[1] : "NULL"));
                chatList.push_back(atoi(row[0]));
            }
            /* free the result set */
            mysql_free_result(result);
            printf("Done.\n");
        }
        return chatList;
    }

    uint32_t getuseridbytoken(const std::string& token) {
        MYSQL_RES *result;
        MYSQL_ROW row;
        int state;
        uint32_t user_id = 0;
        std::string sql = std::string("SELECT user_id FROM sessions WHERE token = '") + token + std::string("'");
        if (m_connect) {
            state = mysql_query(m_connect, sql.c_str());
            if( state != 0 ) {
                printf(mysql_error(m_connect));
                return 0;
            }
            /* must call mysql_store_result() before we can issue any
             * other query calls
             */  
            result = mysql_store_result(m_connect);
            printf("Rows: %d\n", mysql_num_rows(result));
            /* process each row in the result set */
            while( ( row = mysql_fetch_row(result)) != NULL ) {
                printf("id: %s, val: %s\n", 
                       (row[0] ? row[0] : "NULL"), 
                       (row[1] ? row[1] : "NULL"));
                user_id = atoi(row[0]);
            }
            /* free the result set */
            mysql_free_result(result);
            printf("Done.\n");
            return user_id;
        }
        else {
            return 0;
        }
    }
private:
    typedef std::set<connection_hdl,std::owner_less<connection_hdl> > con_list;

    server m_server;
    con_list m_connections;
    std::map<uint32_t, con_list> m_roomConns;
    std::queue<action> m_actions;

    mutex m_action_lock;
    mutex m_connection_lock;
    condition_variable m_action_cond;
    MYSQL *m_connect;
};

int main() {
    try {
    broadcast_server server_instance;

    // Start a thread to run the processing loop
    thread t(bind(&broadcast_server::process_messages,&server_instance));

    // Run the asio loop with the main thread
    server_instance.run(9527);

    t.join();

    } catch (websocketpp::exception const & e) {
        std::cout << e.what() << std::endl;
    }
}
