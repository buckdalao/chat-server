@extends('chat.common.body')
@section('css')
    <style>
        canvas { width: 100%; height: 100% }
        .messagebox {
            border: 1px solid black;
            padding: 5px;
            width: 450px;
        }

        .buttonright {
            float: right;
        }

        .buttonleft {
            float: left;
        }

        .controlbox {
            padding: 5px;
            width: 450px;
            height: 28px;
        }
    </style>
@endsection
@section('body_cont')
    <div class="canvas">
        <div class="controlbox">
            <button id="connectButton" name="connectButton" class="buttonleft">
                Connect
            </button>
            <button id="callButton" name="callButton" class="buttonleft">
                Call
            </button>
            <button id="disconnectButton" name="disconnectButton" class="buttonright" disabled>
                Disconnect
            </button>
        </div>

        <div class="messagebox">
            <label for="message">Enter a message:
                <input type="text" name="message" id="message" placeholder="Message text"
                       inputmode="latin" size=60 maxlength=120 disabled>
                <input type="file" name="file" id="file">
            </label>
            <button id="sendButton" name="sendButton" class="buttonright" disabled>
                Send
            </button>
        </div>
        <div class="messagebox" id="receivebox">
            <p>Messages received:</p>
        </div>
    </div>
@endsection
@section('script')
    <script src ="https://unpkg.com/peerjs@1.0.0/dist/peerjs.min.js"></script>
    <script>
        var isRoot = '{{ $isRoot }}'
        var pageName = '{{ $pageName }}'
    </script>
    <script>
        (function() {
            // Define "global" variables

            var connectButton = null;
            var disconnectButton = null;
            var sendButton = null;
            var messageInputBox = null;
            var receiveBox = null;
            var callButton = null;
            var fileInput = null

            var localConnection = null;   // RTCPeerConnection for our "local" connection
            var remoteConnection = null;  // RTCPeerConnection for the "remote"

            var sendChannel = null;       // RTCDataChannel for the local (sender)
            var receiveChannel = null;    // RTCDataChannel for the remote (receiver)

            // Functions

            // Set things up, connect event listeners, etc.

            function startup() {
                connectButton = document.getElementById('connectButton');
                disconnectButton = document.getElementById('disconnectButton');
                sendButton = document.getElementById('sendButton');
                messageInputBox = document.getElementById('message');
                receiveBox = document.getElementById('receivebox');
                callButton = document.getElementById('callButton');
                fileInput = document.getElementById('file');

                // Set event listeners for user interface widgets

                connectButton.addEventListener('click', connectPeers, false);
                disconnectButton.addEventListener('click', disconnectPeers, false);
                sendButton.addEventListener('click', sendMessage, false);
                callButton.addEventListener('click', call, false);
            }

            var signaling = new WebSocket('ws://192.168.10.10:8877')
            signaling.onopen = onopensocket
            signaling.onmessage = onmessage
            signaling.onerror = socketError
            signaling.onclose = socketClose

            // Connect the two peers. Normally you look for and connect to a remote
            // machine here, but we're just connecting two local objects, so we can
            // bypass that step.

            function connectPeers() {
                // Create the local connection and its event listeners

                localConnection = new RTCPeerConnection();
                localConnection.ontrack = (e) => {
                    console.log(e)
                }

                // Create the data channel and establish its event listeners
                sendChannel = localConnection.createDataChannel("sendChannel");
                sendChannel.binaryType = "arraybuffer"
                sendChannel.onopen = handleSendChannelStatusChange;
                sendChannel.onclose = handleSendChannelStatusChange;
                localConnection.ondatachannel = receiveChannelCallback;
                //sendChannel.onmessage = handleReceiveMessage;
                sendChannel.onerror = function(e) {
                    console.log(e)
                }
                //sendChannel.onbufferedamountlow = handleReceiveBuffer;

                // Create the remote connection and its event listeners

                //remoteConnection = new RTCPeerConnection();
                //remoteConnection.ondatachannel = receiveChannelCallback;

                // Set up the ICE candidates for the two peers
                /*localConnection.onicecandidate = e => !e.candidate
                    || remoteConnection.addIceCandidate(e.candidate)
                        .catch(handleAddCandidateError);*/
                localConnection.onicecandidate = function(e) {
                    console.log(e)
                    if (!e.candidate) {
                        return handleAddCandidateError()
                    }
                    signaling.send(JSON.stringify({
                        cmd: 'publish',
                        subject: 'room1',
                        event: 'candidate',
                        data: e.candidate
                    }))
                }


                /*remoteConnection.onicecandidate = e => !e.candidate
                || localConnection.addIceCandidate(e.candidate)
                    .catch(handleAddCandidateError);*/

                // Now create an offer to connect; this starts the process
                /*localConnection.createOffer()
                    .then(offer => localConnection.setLocalDescription(offer))
                    .then(() => {
                        remoteConnection.setRemoteDescription(localConnection.localDescription)
                        console.log(signaling)
                        signaling.send(JSON.stringify({
                            cmd: 'publish',
                            subject: 'room1',
                            event: 'offer',
                            data: localConnection.localDescription
                        }))
                    })
                    .then(() => remoteConnection.createAnswer())
                    .then((answer) => {
                      remoteConnection.setLocalDescription(answer)
                    })
                    .then(() => localConnection.setRemoteDescription(remoteConnection.localDescription))
                    .catch(handleCreateDescriptionError);*/
            }

            function call(){
                localConnection.createOffer()
                    .then(offer => localConnection.setLocalDescription(offer))
                    .then(() => {
                        //remoteConnection.setRemoteDescription(localConnection.localDescription)
                        signaling.send(JSON.stringify({
                            cmd: 'publish',
                            subject: 'room1',
                            event: 'offer',
                            data: localConnection.localDescription
                        }))
                    }).catch(handleCreateDescriptionError);
            }
            function answer(localDescription){
                localConnection.setRemoteDescription(localDescription)
                localConnection.createAnswer()
                    .then(answer => localConnection.setLocalDescription(answer))
                    .then(() => {
                        // localConnection.setRemoteDescription(remoteConnection.localDescription)
                        signaling.send(JSON.stringify({
                            cmd: 'publish',
                            subject: 'room1',
                            event: 'answer',
                            data: localConnection.localDescription
                        }))
                    }).catch(handleCreateDescriptionError);
            }

            function onopensocket(){
                signaling.send(JSON.stringify({
                    cmd: 'subscribe',
                    subject: 'room1'
                }))
            }
            function onmessage(mes){
                var res = evil(mes.data)
                switch (res.event) {
                    case 'offer':
                        console.log(res.data)
                        answer(res.data)
                        break;
                    case 'answer':
                        localConnection.setRemoteDescription(res.data)
                        console.log(res.data)
                        break;
                    case 'candidate':
                        localConnection.addIceCandidate(res.data)
                        console.log(res.data)
                        break;
                }
            }
            function socketError(){
                console.log('socket error')
            }
            function socketClose(){
                console.log('close socket')
            }
            var evil = function (fn) {
                // 一个变量指向Function，防止有些前端编译工具报错
                let Fn = Function
                return new Fn('return ' + fn)()
            }

            // Handle errors attempting to create a description;
            // this can happen both when creating an offer and when
            // creating an answer. In this simple example, we handle
            // both the same way.

            function handleCreateDescriptionError(error) {
                console.log("Unable to create an offer: " + error.toString());
            }

            // Handle successful addition of the ICE candidate
            // on the "local" end of the connection.

            function handleLocalAddCandidateSuccess() {
                connectButton.disabled = true;
            }

            // Handle successful addition of the ICE candidate
            // on the "remote" end of the connection.

            function handleRemoteAddCandidateSuccess() {
                disconnectButton.disabled = false;
            }

            // Handle an error that occurs during addition of ICE candidate.

            function handleAddCandidateError() {
                console.log("Oh noes! addICECandidate failed!");
            }

            // Handles clicks on the "Send" button by transmitting
            // a message to the remote peer.

            function sendMessage() {
                var message = messageInputBox.value;
                if (message) {
                    sendChannel.send(message);
                }
                var file = fileInput.files[0]
                if (file) {
                    var read = new FileReader()
                    read.readAsArrayBuffer(file)
                    read.onloadend = () => {
                        sendChannel.send(read.result)
                    }
                }

                // Clear the input box and re-focus it, so that we're
                // ready for the next message.

                messageInputBox.value = "";
                messageInputBox.focus();
            }

            function handleReceiveBuffer(e){
                console.log(e)
            }

            // Handle status changes on the local end of the data
            // channel; this is the end doing the sending of data
            // in this example.

            function handleSendChannelStatusChange(event) {
                if (sendChannel) {
                    var state = sendChannel.readyState;

                    if (state === "open") {
                        messageInputBox.disabled = false;
                        messageInputBox.focus();
                        sendButton.disabled = false;
                        disconnectButton.disabled = false;
                        connectButton.disabled = true;
                    } else {
                        messageInputBox.disabled = true;
                        sendButton.disabled = true;
                        connectButton.disabled = false;
                        disconnectButton.disabled = true;
                    }
                }
            }

            // Called when the connection opens and the data
            // channel is ready to be connected to the remote.

            function receiveChannelCallback(event) {
                receiveChannel = event.channel;
                receiveChannel.onmessage = handleReceiveMessage;
                receiveChannel.onopen = handleReceiveChannelStatusChange;
                receiveChannel.onclose = handleReceiveChannelStatusChange;
                receiveChannel.onerror = function(e) {
                    console.log(e)
                }
            }

            // Handle onmessage events for the receiving channel.
            // These are the data messages sent by the sending channel.

            function handleReceiveMessage(event) {
                if (Object.prototype.toString.call(event.data) == '[object ArrayBuffer]') {
                    //var blobData = new Blob([event.data])
                    //console.log(blobData)
                    var bytes = new Uint8Array(event.data);
                    var binary = '';
                    for (var len = bytes.byteLength, i = 0; i < len; i++) {
                        binary += String.fromCharCode(bytes[i]);
                    }
                    var base64 = 'data:image/png;base64,' + window.btoa(binary);
                    var el = document.createElement("img")
                    el.src = base64;
                } else {
                    var el = document.createElement("p");
                    var txtNode = document.createTextNode(event.data);
                    console.log(event)

                    el.appendChild(txtNode);
                }
                receiveBox.appendChild(el);
            }

            // Handle status changes on the receiver's channel.

            function handleReceiveChannelStatusChange(event) {
                if (receiveChannel) {
                    console.log("Receive channel's status has changed to " +
                        receiveChannel.readyState);
                }

                // Here you would do stuff that needs to be done
                // when the channel's status changes.
            }

            // Close the connection, including data channels if they're open.
            // Also update the UI to reflect the disconnected status.

            function disconnectPeers() {

                // Close the RTCDataChannels if they're open.

                sendChannel.close();
                receiveChannel.close();

                // Close the RTCPeerConnections

                localConnection.close();
                //remoteConnection.close();

                sendChannel = null;
                receiveChannel = null;
                localConnection = null;
                remoteConnection = null;

                // Update user interface elements

                connectButton.disabled = false;
                disconnectButton.disabled = true;
                sendButton.disabled = true;

                messageInputBox.value = "";
                messageInputBox.disabled = true;
            }

            // Set up an event listener which will run the startup
            // function once the page is done loading.

            window.addEventListener('load', startup, false);
        })();
    </script>
@endsection
