CC=g++ -std=c++11 -fpermissive
CFLAGS=-I../websocketpp -I/usr/include/mysql -lboost_system -lpthread  `mysql_config --cflags` `mysql_config --libs`
OBJ = chat_server.o

%.o: %.cpp
	$(CC) -c -o $@ $< $(CFLAGS)

chatServer: $(OBJ)
	$(CC) -o $@ $^ $(CFLAGS)

