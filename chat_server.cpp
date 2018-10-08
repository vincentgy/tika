#include <websocketpp/config/asio_no_tls.hpp>

#include <websocketpp/server.hpp>
#include <websocketpp/frame.hpp>
#include <iostream>
#include <set>
#include <map>
#include <unordered_map>
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
#define MAX_SIZE  4096
#define BYTE  unsigned char

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
    LASTSEEN = 8,
    MYUSERID = 9,
    JOINRANGE = 10,
    HISTDONE = 11,
    TRUNCATE = 12
};

struct command
{
    command(): opcode(0), chatId(0), userId(0), messageId(0), count(0), token(std::string()), message(std::string()) {
    }
    uint32_t opcode;
    uint32_t chatId;
    uint32_t userId;
    uint64_t messageId;
    uint64_t messageId2;
    uint32_t count;
    uint32_t timestamp;
    uint32_t updated;
    std::string token;
    std::string message;
    std::vector<uint32_t> userList;
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

std::string pack64le (uint64_t x) {
    std::string r;

    for (int i = 8; i--;) {
        r += ((char)(x & 255));
        x >>= 8;
    }

    return r;
}

uint64_t unpack64le(const std::string& x) {
    uint64_t r = 0;

    for (int i = 8; i--;) {
        BYTE b = (BYTE)(x[i]);
        r = ((r << 8) >> 0) + (uint64_t)(b);
    }

    return r;
}

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
        BYTE b = (BYTE)(x[i]);
        r = ((r << 8) >> 0) + (uint32_t)(b);
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

uint16_t unpack16le(const std::string& x) {
    uint16_t r = 0;

    for (int i = 2; i--;) {
        BYTE b = (BYTE)(x[i]);
        r = ((r << 8) >> 0) + (uint16_t)(b);
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
    if (OPCODE::CLIENTID == opcode) {
        len = unpack16le(cmdq.substr(1, 2));
        std::string token = cmdq.substr(3, len);
        r.token = token;
    }
    else if (OPCODE::JOIN == opcode) {
        std::cout<<"parse JOIN"<<std::endl;
        r.chatId = unpack32le(cmdq.substr(1, 4));
        r.userId = unpack32le(cmdq.substr(5, 4));
    }
    else if (OPCODE::NEWMSG == opcode || OPCODE::OLDMSG == opcode) {
        r.chatId = unpack32le(cmdq.substr(1, 4));
        r.userId = unpack32le(cmdq.substr(5, 4));
        len = unpack16le(cmdq.substr(9, 2));
        r.message = cmdq.substr(11, len);
    }
    else if (OPCODE::NEWROOM == opcode) {
        r.userId = unpack32le(cmdq.substr(1, 4));
        len = unpack16le(cmdq.substr(5, 2));
        for (int ui = 0; ui < len; ui++) {
            r.userList.push_back(unpack32le(cmdq.substr(7 + (ui * 4), 4)));
        }
    }
    else if (OPCODE::LASTSEEN == opcode) {
        r.chatId = unpack32le(cmdq.substr(1, 4));
        r.userId = unpack32le(cmdq.substr(5, 4));
        r.messageId = unpack64le(cmdq.substr(9, 8));
    }
    else if (OPCODE::HIST == opcode) {
        r.chatId = unpack32le(cmdq.substr(1, 4));
        r.userId = unpack32le(cmdq.substr(5, 4));
        r.count = unpack16le(cmdq.substr(9, 2));
    }
    else if (OPCODE::JOINRANGE == opcode) {
        r.chatId = unpack32le(cmdq.substr(1, 4));
        r.userId = unpack32le(cmdq.substr(5, 4));
        r.messageId = unpack64le(cmdq.substr(9, 8));//latest message ID
        r.messageId2 = unpack64le(cmdq.substr(17, 8));//oldest message ID
    }
    else if (OPCODE::TRUNCATE == opcode) {
        r.chatId = unpack32le(cmdq.substr(1, 4));
        r.userId = unpack32le(cmdq.substr(5, 4));
    }
}

std::string assemble_cmd(const command& cmd) {
    std::string buf;
    buf += ((char)cmd.opcode);

    if(OPCODE::JOIN == cmd.opcode) {
        buf += pack32le(cmd.chatId);
        buf += pack32le(cmd.userId);
    }
    else if(OPCODE::NEWMSG == cmd.opcode || OPCODE::OLDMSG == cmd.opcode) {
        buf += pack32le(cmd.chatId);
        buf += pack32le(cmd.userId);
        buf += pack64le(cmd.messageId);
        buf += pack32le(cmd.timestamp);
        buf += pack32le(cmd.updated);
        buf += pack16le(cmd.message.length());
        buf += cmd.message;
    }
    else if (OPCODE::CHATLIST ==  cmd.opcode) {
        buf += pack16le(cmd.chatList.size());
        for(int i = 0; i < cmd.chatList.size(); i++) {
            buf += pack32le(cmd.chatList[i]);
        }
    }
    else if (OPCODE::NEWROOM == cmd.opcode) {
        buf += cmd.chatId;
    }
    else if (OPCODE::LASTSEEN == cmd.opcode) {
        buf += pack32le(cmd.chatId);
        buf += pack32le(cmd.userId);
        buf += pack64le(cmd.messageId);
    }
    else if (OPCODE::HISTDONE == cmd.opcode) {
        buf += pack32le(cmd.chatId);
        buf += pack32le(cmd.userId);
    }
    else if (OPCODE::TRUNCATE == cmd.opcode) {
        buf += pack32le(cmd.chatId);
        buf += pack32le(cmd.userId);
        buf += pack64le(cmd.messageId);
    }
    return buf;
}
class chatServer {
public:
    chatServer() {
        // Initialize Asio Transport
        m_server.init_asio();

        // Register handler callbacks
        m_server.set_open_handler(bind(&chatServer::on_open,this,::_1));
        m_server.set_close_handler(bind(&chatServer::on_close,this,::_1));
        m_server.set_message_handler(bind(&chatServer::on_message,this,::_1,::_2));

        m_connect = mysql_init(NULL);
        if (!m_connect)
        {
            std::cout << "Mysql Initialization Failed" << std::endl;
        }
        mysql_options(m_connect, MYSQL_SET_CHARSET_NAME, "utf8");
        mysql_options(m_connect, MYSQL_INIT_COMMAND, "SET NAMES utf8");
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
    ~chatServer() {
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
                for (auto rit = m_roomConns.begin(); rit != m_roomConns.end(); ++ rit) {
                    rit->second.erase(a.hdl);
                }
                for (auto uit = m_userConns.begin(); uit != m_userConns.end(); ++ uit) {
                    uit->second.erase(a.hdl);
                }
                this->m_connCursors.erase(a.hdl);
            } else if (a.type == MESSAGE) {
                lock_guard<mutex> guard(m_connection_lock);
                command cmd;
                std::string response_str;
                parse_cmd(a.msg->get_payload(), cmd);
                std::cout<< "cmd opCode:" << cmd.opcode<<std::endl;

                if (OPCODE::CLIENTID == cmd.opcode) {
                        uint32_t user_id = getuseridbytoken(cmd.token);
                        if (user_id > 0) {
                            if (m_userConns.find(user_id) == m_userConns.end()) {
                                m_userConns[user_id] = con_list();
                            }
                            std::string myid_str;
                            myid_str += ((char)OPCODE::MYUSERID);
                            myid_str += pack32le(user_id);
                            m_server.send(a.hdl, myid_str,  a.msg->get_opcode());
                            m_userConns[user_id].insert(a.hdl);
                            std::cout<<"CLIENTID:"<<user_id<<','<<std::endl;
                            cmd.opcode = OPCODE::CHATLIST;
                            std::vector<command> newMessages;
                            cmd.chatList = getchatlist(user_id);
                            response_str = assemble_cmd(cmd);
                            m_server.send(a.hdl, response_str,  a.msg->get_opcode());
                        }
                    }
                    else if (OPCODE::JOIN == cmd.opcode) {
                        std::cout<<"received JOIN"<<std::endl;
                        std::vector<uint32_t> userList = getparticipants(cmd.chatId);
                        // add connection to room's connection set.
                        if (m_roomConns.find(cmd.chatId) == m_roomConns.end()) {
                            m_roomConns[cmd.chatId] = con_list();
                        }
                        m_roomConns[cmd.chatId].insert(a.hdl);
                        // add connection to user's connection set.
                        if (m_userConns.find(cmd.userId) == m_userConns.end()) {
                            m_userConns[cmd.userId] = con_list();
                        }
                        m_userConns[cmd.userId].insert(a.hdl);
                        // update intial connection cursor.
                        m_connCursors[a.hdl][cmd.chatId] = this->getchatlastmsgid(cmd.chatId) + 1;
                        for (int index = 0; index < userList.size();index++) {
                            std::cout<< 'JOINED:'<<userList[index]<<std::endl;
                            std::string str;
                            str += ((char)cmd.opcode);
                            str += pack32le(cmd.chatId);
                            str += pack32le(userList[index]);
                            m_server.send(a.hdl, str, a.msg->get_opcode());
                        }
                        // response with lastseen.
                        cmd.opcode = OPCODE::LASTSEEN;
                        cmd.messageId = this->getlastseen(cmd.chatId, cmd.userId);
                        std::string lastseen_str =  assemble_cmd(cmd);
                        m_server.send(a.hdl, lastseen_str,  a.msg->get_opcode());
                    }
                    else if(OPCODE::NEWMSG == cmd.opcode) {
                        std::cout<<"NEWMSG:"<<cmd.chatId<<','<<cmd.userId<<','<<cmd.message<<std::endl;
                        auto nmsg = addchatmessage(cmd.chatId, cmd.userId, cmd.message);
                        setlastseen(cmd.chatId, cmd.userId, cmd.messageId);
                        std::cout<<"message id"<<cmd.messageId<<std::endl;
                        response_str = assemble_cmd(nmsg);
                        for(int i=0;i<response_str.length();i++) {
                            std::cout<<std::hex<<(uint32_t)response_str[i];
                        }
                        std::cout<<std::endl;
                        sendToRoom(cmd.chatId, response_str, a.msg->get_opcode());
                    }
                    else if(OPCODE::NEWROOM == cmd.opcode) {
                        uint32_t cId = createchat(cmd.userId, cmd.userList);
                        cmd.chatId = cId;
                        response_str = assemble_cmd(cmd);
                        for (int i = 0; i < cmd.userList.size(); i++) {
                            sendToUser(cmd.userList[i], response_str, a.msg->get_opcode());
                        }
                    }
                    else if(OPCODE::LASTSEEN == cmd.opcode) {
                        setlastseen(cmd.chatId, cmd.userId, cmd.messageId);
                        response_str = assemble_cmd(cmd);
                        sendToUser(cmd.userId, response_str, a.msg->get_opcode());
                    }
                    else if(OPCODE::HIST == cmd.opcode) {
                        std::vector<command> roomMessages = this->getchatmessages(cmd.chatId, this->m_connCursors[a.hdl][cmd.chatId], cmd.count);
                        // flush all new messages;
                        for (int nIndex = 0; nIndex < roomMessages.size(); nIndex++) {
                            std::string response_str = assemble_cmd(roomMessages[nIndex]);
                            m_server.send(a.hdl, response_str,  a.msg->get_opcode());
                            this->m_connCursors[a.hdl][cmd.chatId] = roomMessages[nIndex].messageId;
                        }
                        cmd.opcode = HISTDONE;
                        response_str = assemble_cmd(cmd);
                        sendToUser(cmd.userId, response_str, a.msg->get_opcode());
                    }
                    else if(OPCODE::JOINRANGE == cmd.opcode) {
                        std::vector<command> roomNewMessages = getchatnewmessages(cmd.chatId, cmd.messageId);
                        // flush all new messages;
                        for (int nIndex = 0; nIndex < roomNewMessages.size(); nIndex++) {
                            std::string response_str = assemble_cmd(roomNewMessages[nIndex]);
                            m_server.send(a.hdl, response_str,  a.msg->get_opcode());
                        }
                        this->m_connCursors[a.hdl][cmd.chatId] = cmd.messageId2;
                    }
                    else if(OPCODE::TRUNCATE == cmd.opcode) {
                        uint64_t dMessageId = this->truncatechat(cmd.chatId, cmd.userId);
                        cmd.messageId = dMessageId;
                        response_str = assemble_cmd(cmd);
                        sendToRoom(cmd.chatId, response_str, a.msg->get_opcode());
                    }
            } else {
                // undefined.
            }
        }
    }
protected:
    void sendToRoom(uint32_t chat_id, const server::message_ptr& str) {
        std::cout<<"SEND to room " << chat_id<<std::endl;
        for (con_list::iterator it = m_roomConns[chat_id].begin(); it != m_roomConns[chat_id].end(); ++it) {
            std::cout<<"SEND to user"<<std::endl;
            m_server.send(*it, str);
        }
    }
    void sendToRoom(uint32_t chat_id, const std::string& str, websocketpp::frame::opcode::value mOpcode = websocketpp::frame::opcode::value::TEXT) {
        std::cout<<"SEND to room " << chat_id<<std::endl;
        for (con_list::iterator it = m_roomConns[chat_id].begin(); it != m_roomConns[chat_id].end(); ++it) {
            std::cout<<"SEND to user "<<std::endl;
            m_server.send(*it, str, mOpcode);
        }
    }
    void sendToUser(uint32_t user_id, const std::string& str, websocketpp::frame::opcode::value mOpcode = websocketpp::frame::opcode::value::TEXT) {
        for (con_list::iterator it = m_userConns[user_id].begin(); it != m_userConns[user_id].end(); ++it) {
            m_server.send(*it, str, mOpcode);
        }
    }

    command addchatmessage(uint32_t chat_id, uint32_t user_id, const std::string& message) {
        MYSQL_STMT *stmt;
        MYSQL_BIND bind[4];
        uint32_t timestamp = time(NULL);
        command item;
        item.opcode = OPCODE::NEWMSG;
        item.chatId = chat_id;
        item.userId = user_id;
        item.message = message;
        item.timestamp = timestamp;

        std::string sql = std::string("INSERT INTO chat_messages (chat_id, user_id, message, timestamp) VALUES (?,?,?,?)");
        std::cout<<"sql:"<<sql<<std::endl;
        if (m_connect) {
            stmt = mysql_stmt_init(m_connect);
            if(stmt == NULL) {
                printf(mysql_error(m_connect));
                return item;
            }
            mysql_stmt_prepare(stmt, sql.c_str(), sql.length());
            // bind parameters set.
            memset(bind, 0, sizeof(bind));
            bind[0].buffer_type= MYSQL_TYPE_LONG;
            bind[0].buffer= (char*)&chat_id;
            bind[1].buffer_type= MYSQL_TYPE_LONG;
            bind[1].buffer= (char*)&user_id;
            bind[2].buffer_type= MYSQL_TYPE_STRING;
            bind[2].buffer= (void*)message.c_str();
            bind[2].buffer_length= message.length();
            bind[3].buffer_type= MYSQL_TYPE_LONG;
            bind[3].buffer= (char*)&timestamp;

            mysql_stmt_bind_param(stmt, bind);
            if (!mysql_stmt_execute(stmt)) {
                item.messageId = (uint64_t)mysql_insert_id(m_connect);;
            }
            else {
                printf(mysql_error(m_connect));
            }
            mysql_stmt_close(stmt);
        }
        return item;
    }

    bool addchatuser (uint32_t chat_id, uint32_t user_id) {
        MYSQL_STMT *stmt;
        MYSQL_BIND bind[2];
        bool result = false;
        std::string sql = std::string("INSERT INTO chat_users (chat_id, user_id) VALUES (?, ?)");
        std::cout<<"sql:"<<sql<<std::endl;
        if (m_connect) {
            stmt = mysql_stmt_init(m_connect);
            if(stmt == NULL) {
                printf(mysql_error(m_connect));
                return false;
            }
            mysql_stmt_prepare(stmt, sql.c_str(), sql.length());
            // bind parameters set.
            memset(bind, 0, sizeof(bind));
            bind[0].buffer_type= MYSQL_TYPE_LONG;
            bind[0].buffer= (char*)&chat_id;
            bind[1].buffer_type= MYSQL_TYPE_LONG;
            bind[1].buffer= (char*)&user_id;
            mysql_stmt_bind_param(stmt, bind);
            if (!mysql_stmt_execute(stmt)) {
                result = true;
            }
            mysql_stmt_close(stmt);
        }
        return result;
    }

    uint32_t createchat(uint32_t user_id, const std::vector<uint32_t>& users) {
        int state;
        uint32_t newId = 0;
        std::string sql;
        // 1on1 chat
        if (users.size() == 2) {
            MYSQL_RES *result;
            MYSQL_ROW row;
            sql = std::string("SELECT chat_id FROM chat_users WHERE user_id = ") + std::to_string(users[0]) +
                  std::string(" AND chat_id IN (SELECT chat_id FROM chat_users WHERE user_id = ") + std::to_string(users[1]) + std::string(")");
            std::cout<<"sql:"<<sql<<std::endl;
            if (m_connect) {
                state = mysql_query(m_connect, sql.c_str());
                if( state != 0 ) {
                    printf(mysql_error(m_connect));
                    return false;
                }
                /* must call mysql_store_result() before we can issue any
                 * other query calls
                 */  
                result = mysql_store_result(m_connect);
                if (mysql_num_rows(result) == 1) {
                    row = mysql_fetch_row(result);
                    if (row != NULL) {
                        newId = atoi(row[0]);
                    }
                }
                mysql_free_result(result);
                /* free the result set */
                printf("Done.\n");
                /* chat already exits */
                if (newId > 0) {
                    return newId;
                }
            }     
        }
        sql = std::string("INSERT INTO chats (user_id, created_at) VALUES (") +
                            std::to_string(user_id) + std::string(",") + std::string("UNIX_TIMESTAMP())");
        std::cout<<"sql:"<<sql<<std::endl;
        if (m_connect) {
            state = mysql_query(m_connect, sql.c_str());
            if( state != 0 ) {
                printf(mysql_error(m_connect));
                return false;
            }
            else {
                newId = (uint32_t)mysql_insert_id(m_connect);
                for (int i = 0; i < users.size(); i++) {
                    addchatuser(newId, users[i]);
                }
            }
        }
        return newId;
    }

    bool setlastseen (uint32_t chat_id, uint32_t user_id, uint64_t message_id) {
        MYSQL_STMT *stmt;
        MYSQL_BIND bind[3];
        bool result = false;
        std::string sql = std::string("UPDATE chat_users SET last_seen=? WHERE chat_id=? AND user_id=?");
        std::cout<<"sql:"<<sql<<std::endl;
        if (m_connect) {
            stmt = mysql_stmt_init(m_connect);
            if(stmt == NULL) {
                printf(mysql_error(m_connect));
                return false;
            }
            mysql_stmt_prepare(stmt, sql.c_str(), sql.length());
            // bind parameters set.
            memset(bind, 0, sizeof(bind));
            bind[0].buffer_type= MYSQL_TYPE_LONGLONG;
            bind[0].buffer= (char*)&message_id;
            bind[1].buffer_type= MYSQL_TYPE_LONG;
            bind[1].buffer= (char*)&chat_id;
            bind[2].buffer_type= MYSQL_TYPE_LONG;
            bind[2].buffer= (char*)&user_id;
            mysql_stmt_bind_param(stmt, bind);
            if (!mysql_stmt_execute(stmt)) {
                result = true;
            }
            mysql_stmt_close(stmt);
        }
        return result;
    }

    uint64_t getchatlastmsgid(uint32_t chat_id) {
        MYSQL_RES *result;
        MYSQL_ROW row;
        MYSQL_STMT *stmt;
        MYSQL_BIND bind[1];
        MYSQL_BIND bResult[1];
        uint64_t init_data = 0;

        std::string sql = std::string("SELECT id FROM chat_messages WHERE chat_id = ? ORDER BY id DESC limit 1");
        std::cout<<"sql:"<<sql<<std::endl;
        if (m_connect) {
            stmt = mysql_stmt_init(m_connect);
            if(stmt == NULL) {
                printf(mysql_error(m_connect));
                return init_data;
            }
            mysql_stmt_prepare(stmt, sql.c_str(), sql.length());
            // bind parameters set.
            memset(bind, 0, sizeof(bind));
            bind[0].buffer_type= MYSQL_TYPE_LONG;
            bind[0].buffer= (char*)&chat_id;
            mysql_stmt_bind_param(stmt, bind);

            // bind result set.
            memset(bResult, 0, sizeof(bResult));
            bResult[0].buffer_type= MYSQL_TYPE_LONGLONG;
            bResult[0].buffer= (char *)&init_data;
            mysql_stmt_bind_result(stmt, bResult);
            /* must call mysql_store_result() before we can issue any
             * other query calls
             */
            mysql_stmt_execute(stmt);
\
            mysql_stmt_fetch(stmt);
            std::cout<<"message ID :"<< init_data <<std::endl;
            mysql_stmt_close(stmt);
            printf("Done.\n");
        }
        return init_data;
    }

    uint64_t truncatechat(uint32_t chat_id, uint32_t user_id) {
        uint64_t result = 0;
        if (checkchatuser(chat_id, user_id) == false) {
            return result;
        }
        uint64_t messageId = getchatlastmsgid(chat_id);
        MYSQL_STMT *stmt;
        MYSQL_BIND bind[2];

        std::string sql = std::string("DELETE FROM chat_messages WHERE chat_id=? AND id <=?");
        std::cout<<"sql:"<<sql<<std::endl;
        if (m_connect) {
            stmt = mysql_stmt_init(m_connect);
            if(stmt == NULL) {
                printf(mysql_error(m_connect));
                return false;
            }
            mysql_stmt_prepare(stmt, sql.c_str(), sql.length());
            // bind parameters set.
            memset(bind, 0, sizeof(bind));
            bind[0].buffer_type= MYSQL_TYPE_LONG;
            bind[0].buffer= (char*)&chat_id;
            bind[1].buffer_type= MYSQL_TYPE_LONGLONG;
            bind[1].buffer= (char*)&messageId;
            mysql_stmt_bind_param(stmt, bind);
            if (!mysql_stmt_execute(stmt)) {
                result = messageId;
            }
            mysql_stmt_close(stmt);
        }
        return result;
    }

    bool checkchatuser(uint32_t chat_id, uint32_t user_id) {
        MYSQL_ROW row;
        MYSQL_STMT *stmt;
        MYSQL_BIND bind[2];
        MYSQL_BIND bResult[1];
        uint64_t init_data = 0;
        bool result = false;

        std::string sql = std::string("SELECT user_id FROM chat_users WHERE chat_id = ? AND user_id = ?");
        std::cout<<"sql:"<<sql<<std::endl;
        if (m_connect) {
            stmt = mysql_stmt_init(m_connect);
            if(stmt == NULL) {
                printf(mysql_error(m_connect));
                return init_data;
            }
            mysql_stmt_prepare(stmt, sql.c_str(), sql.length());
            // bind parameters set.
            memset(bind, 0, sizeof(bind));
            bind[0].buffer_type= MYSQL_TYPE_LONG;
            bind[0].buffer= (char*)&chat_id;
            bind[1].buffer_type= MYSQL_TYPE_LONG;
            bind[1].buffer= (char*)&user_id;
            mysql_stmt_bind_param(stmt, bind);

            // bind result set.
            memset(bResult, 0, sizeof(bResult));
            bResult[0].buffer_type= MYSQL_TYPE_LONGLONG;
            bResult[0].buffer= (char *)&init_data;
            mysql_stmt_bind_result(stmt, bResult);
            /* must call mysql_store_result() before we can issue any
             * other query calls
             */
            mysql_stmt_execute(stmt);
            mysql_stmt_fetch(stmt);
            if (mysql_stmt_num_rows(stmt) == 1) {
                result = true;
            }
            mysql_stmt_close(stmt);
            printf("Done.\n");
        }
        return result;
    }

    uint64_t getlastseen(uint32_t chat_id, uint32_t user_id) {
        MYSQL_RES *result;
        MYSQL_ROW row;
        MYSQL_STMT *stmt;
        MYSQL_BIND bind[2];
        MYSQL_BIND bResult[1];
        uint64_t init_data = 0;

        std::string sql = std::string("SELECT last_seen FROM chat_users WHERE chat_id = ? AND user_id = ?");
        std::cout<<"sql:"<<sql<<std::endl;
        if (m_connect) {
            stmt = mysql_stmt_init(m_connect);
            if(stmt == NULL) {
                printf(mysql_error(m_connect));
                return init_data;
            }
            mysql_stmt_prepare(stmt, sql.c_str(), sql.length());
            // bind parameters set.
            memset(bind, 0, sizeof(bind));
            bind[0].buffer_type= MYSQL_TYPE_LONG;
            bind[0].buffer= (char*)&chat_id;
            bind[1].buffer_type= MYSQL_TYPE_LONG;
            bind[1].buffer= (char*)&user_id;
            mysql_stmt_bind_param(stmt, bind);

            // bind result set.
            memset(bResult, 0, sizeof(bResult));
            bResult[0].buffer_type= MYSQL_TYPE_LONGLONG;
            bResult[0].buffer= (char *)&init_data;
            mysql_stmt_bind_result(stmt, bResult);
            /* must call mysql_store_result() before we can issue any
             * other query calls
             */
            mysql_stmt_execute(stmt);
\
            mysql_stmt_fetch(stmt);
            mysql_stmt_close(stmt);
            printf("Done.\n");
        }
        return init_data;
    }

    std::vector<command> getchatmessages(uint32_t chat_id, uint64_t start, uint32_t count) {
        MYSQL_RES *result;
        MYSQL_ROW row;
        MYSQL_STMT *stmt;
        MYSQL_BIND bind[3];
        MYSQL_BIND bResult[6];
        bool ready = false;

        std::vector<command> cmdList;
        std::string sql = std::string("SELECT * FROM chat_messages WHERE chat_id = ? AND id < ? ORDER BY id DESC limit ?");
        std::cout<<"chat:"<<chat_id<<"start:"<<start<<"count:"<<count<<std::endl;
        std::cout<<"sql:"<<sql<<std::endl;
        if (m_connect) {
            stmt = mysql_stmt_init(m_connect);
            if(stmt == NULL) {
                printf(mysql_error(m_connect));
                return cmdList;
            }
            mysql_stmt_prepare(stmt, sql.c_str(), sql.length());
            // bind parameters set.
            memset(bind, 0, sizeof(bind));
            bind[0].buffer_type= MYSQL_TYPE_LONG;
            bind[0].buffer= (char*)&chat_id;
            bind[1].buffer_type= MYSQL_TYPE_LONGLONG;
            bind[1].buffer= (char*)&start;
            bind[2].buffer_type= MYSQL_TYPE_LONG;
            bind[2].buffer= (char*)&count;
            mysql_stmt_bind_param(stmt, bind);
            command  result;
            result.opcode = OPCODE::OLDMSG;
            // bind result set.
            char *msgBuffer = new char[MAX_SIZE];
            unsigned long msgLength;
            memset(bResult, 0, sizeof(bResult));
            bResult[0].buffer_type= MYSQL_TYPE_LONGLONG;
            bResult[0].buffer= (char *)&result.messageId;
            bResult[1].buffer_type= MYSQL_TYPE_LONG;
            bResult[1].buffer= (char *)&result.chatId;
            bResult[2].buffer_type= MYSQL_TYPE_LONG;
            bResult[2].buffer= (char *)&result.userId;
            bResult[3].buffer_type= MYSQL_TYPE_STRING;
            bResult[3].buffer = msgBuffer;
            bResult[3].buffer_length = MAX_SIZE;
            bResult[3].length = &msgLength;
            bResult[4].buffer_type= MYSQL_TYPE_LONG;
            bResult[4].buffer= (char *)&result.timestamp;
            bResult[5].buffer_type= MYSQL_TYPE_LONG;
            bResult[5].buffer= (char *)&result.updated;
            /* Bind the result buffers */
            if (mysql_stmt_bind_result(stmt, bResult)) {
                fprintf(stderr, " mysql_stmt_bind_result() failed\n");
                fprintf(stderr, " %s\n", mysql_stmt_error(stmt));
            }
            else {
                mysql_stmt_execute(stmt);
                /* Now buffer all results to client (optional step)*/
                if (mysql_stmt_store_result(stmt)) {
                  fprintf(stderr, " mysql_stmt_store_result() failed\n");
                  fprintf(stderr, " %s\n", mysql_stmt_error(stmt));
                }
                else {
                    ready = true;
                }
            }
            while(ready && !mysql_stmt_fetch(stmt)) {
                result.message = std::string(msgBuffer, msgLength);
                memset(msgBuffer, 0, sizeof(msgBuffer));
                std::cout<<"new message:"<<result.messageId<<std::endl;
                cmdList.push_back(result);
            }
            fprintf(stderr, " %s\n", mysql_stmt_error(stmt));
            delete msgBuffer;
            mysql_stmt_close(stmt);
            printf("Done.\n");
        }
        return cmdList;
    }

    std::vector<command> getchatnewmessages(uint32_t chat_id, uint64_t end) {
        MYSQL_RES *result;
        MYSQL_ROW row;
        MYSQL_STMT *stmt;
        MYSQL_BIND bind[2];
        MYSQL_BIND bResult[6];
        bool ready = false;
        std::vector<command> cmdList;
        std::string sql = std::string("SELECT * FROM chat_messages WHERE chat_id = ? AND id > ? ORDER BY id ASC");
        std::cout<<"sql:"<<sql<<std::endl;
        if (m_connect) {
            stmt = mysql_stmt_init(m_connect);
            if(stmt == NULL) {
                printf(mysql_error(m_connect));
                return cmdList;
            }
            mysql_stmt_prepare(stmt, sql.c_str(), sql.length());
            // bind parameters set.
            memset(bind, 0, sizeof(bind));
            bind[0].buffer_type= MYSQL_TYPE_LONG;
            bind[0].buffer= (char*)&chat_id;
            bind[1].buffer_type= MYSQL_TYPE_LONGLONG;
            bind[1].buffer= (char*)&end;
            mysql_stmt_bind_param(stmt, bind);
            command  result;
            result.opcode = OPCODE::NEWMSG;
            // bind result set.
            char *msgBuffer = new char[MAX_SIZE];
            unsigned long msgLength;
            memset(bResult, 0, sizeof(bResult));
            bResult[0].buffer_type= MYSQL_TYPE_LONGLONG;
            bResult[0].buffer= (char *)&result.messageId;
            bResult[1].buffer_type= MYSQL_TYPE_LONG;
            bResult[1].buffer= (char *)&result.chatId;
            bResult[2].buffer_type= MYSQL_TYPE_LONG;
            bResult[2].buffer= (char *)&result.userId;
            bResult[3].buffer_type= MYSQL_TYPE_STRING;
            bResult[3].buffer = msgBuffer;
            bResult[3].buffer_length = MAX_SIZE;
            bResult[3].length = &msgLength;
            bResult[4].buffer_type= MYSQL_TYPE_LONG;
            bResult[4].buffer= (char *)&result.timestamp;
            bResult[5].buffer_type= MYSQL_TYPE_LONG;
            bResult[5].buffer= (char *)&result.updated;
            /* Bind the result buffers */
            if (mysql_stmt_bind_result(stmt, bResult)) {
                fprintf(stderr, " mysql_stmt_bind_result() failed\n");
                fprintf(stderr, " %s\n", mysql_stmt_error(stmt));
            }
            else {
                mysql_stmt_execute(stmt);
                /* Now buffer all results to client (optional step)*/
                if (mysql_stmt_store_result(stmt)) {
                  fprintf(stderr, " mysql_stmt_store_result() failed\n");
                  fprintf(stderr, " %s\n", mysql_stmt_error(stmt));
                }
                else {
                    ready = true;
                }
            }
            while(ready && !mysql_stmt_fetch(stmt)) {
                result.message = std::string(msgBuffer, msgLength);
                memset(msgBuffer, 0, sizeof(msgBuffer));
                std::cout<<"new message:"<<result.messageId<<std::endl;
                cmdList.push_back(result);
            }
            delete msgBuffer;
            mysql_stmt_close(stmt);
            printf("Done.\n");
        }
        return cmdList;
    }

    std::vector<uint32_t> getparticipants(uint32_t chat_id) {
        MYSQL_RES *result;
        MYSQL_ROW row;
        MYSQL_STMT *stmt;
        MYSQL_BIND bind[1];
        MYSQL_BIND bResult[1];
        uint32_t init_data = chat_id;
        std::vector<uint32_t> userList;
        std::string sql = std::string("SELECT user_id FROM chat_users WHERE chat_id = ?");
        std::cout<<"sql:"<<sql<<std::endl;
        if (m_connect) {
            stmt = mysql_stmt_init(m_connect);
            if(stmt == NULL) {
                printf(mysql_error(m_connect));
                return userList;
            }
            mysql_stmt_prepare(stmt, sql.c_str(), sql.length());
            // bind parameters set.
            memset(bind, 0, sizeof(bind));
            bind[0].buffer_type= MYSQL_TYPE_LONG;
            bind[0].buffer= (char*)&init_data;
            mysql_stmt_bind_param(stmt, bind);

            // bind result set.
            memset(bResult, 0, sizeof(bResult));
            bResult[0].buffer_type= MYSQL_TYPE_LONG;
            bResult[0].buffer= (char *)&init_data;
            mysql_stmt_bind_result(stmt, bResult);
            /* must call mysql_store_result() before we can issue any
             * other query calls
             */
            mysql_stmt_execute(stmt);
            mysql_stmt_store_result(stmt);

            while(!mysql_stmt_fetch(stmt)) {
                userList.push_back(init_data);
            }

            mysql_stmt_close(stmt);
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
    typedef std::unordered_map<uint32_t,uint64_t> chat_msg_map;
    typedef std::map<connection_hdl, chat_msg_map, std::owner_less<connection_hdl> > con_map;
    server m_server;
    con_list m_connections;
    std::unordered_map<uint32_t, con_list> m_roomConns;
    std::unordered_map<uint32_t, con_list> m_userConns;
    con_map m_connCursors;
    std::queue<action> m_actions;

    mutex m_action_lock;
    mutex m_connection_lock;
    condition_variable m_action_cond;
    MYSQL *m_connect;
};

int main() {
    try {
    chatServer server_instance;

    // Start a thread to run the processing loop
    thread t(bind(&chatServer::process_messages,&server_instance));

    // Run the asio loop with the main thread
    server_instance.run(9527);

    t.join();

    } catch (websocketpp::exception const & e) {
        std::cout << e.what() << std::endl;
    }
}
