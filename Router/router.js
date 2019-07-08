"use strict";
const net = require('net');
const port = 1276;

const TYPE_SERVER = 0;
const keys = {};

keys[TYPE_SERVER] = [
	"7aa7f59a7933161fcfde942f7fb1a15ba129d91f077f1a3bb204c0389a5e4aba"
];

const sockets = [];
function getSocketsByNames(names){
	const socketsByNames = [];
	for(let i = 0; i < names.length; i++){
		const socketByName = sockets[names[i]];
		if(socketByName !== undefined){
			socketsByNames[socketByName.name] = socketByName;
		}
	}
	return socketsByNames;
}
function getSockets(){
	return sockets;
}
function removeSocket(socket){
	if(!socket.destroyed){
		socket.destroy();
		console.log(socket.name + ' socket has been destroyed');
	}
	sockets.splice(socket.name, 1);
	console.log(socket.name + ' has been removed');
}

function authKeyExists(index, key){
	return keys[index].indexOf(key) !== -1;
}

const server = net.createServer((socket)=>{
	setTimeout(function(){
		if(!socket.authenticated){
			sendJson(socket, {
				"event": "ServerAuthenticateEvent",
				"authenticated": false,
				"message": "No attempts made to try to authenticate"
			});
			removeSocket(socket);
		}
	}, 3000);

	const key = socket.remoteAddress + ':' + socket.remotePort;
	if(sockets[key] === undefined){
		sockets[key] = socket;
		socket.name = key;
		socket.authenticated = false;
		console.log(key + ' connected');
	}else{
		console.log(key + ' connected but was already connected');
	}

	socket.on('data', function(data){
		const json = JSON.parse(data);
		if(!socket.authenticated){
			if(authKeyExists(json.type, json.authKey)){
				socket.authenticated = true;
				sendJson(socket, {
					"event": "ServerAuthenticateEvent",
					"authenticated": true,
					"message": "Server authenticated successfully using authentication key"
				});
			}else{
				sendJson(socket, {
					"event": "ServerAuthenticateEvent",
					"authenticated": false,
					"message": "Server authentication failed"
				});
				removeSocket(socket);
			}
			return false;
		}else{

		}
	});
	socket.on('end', function(){
		removeSocket(socket);
	});
});
server.listen(port);
