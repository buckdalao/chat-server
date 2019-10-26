const webrtc = {
    peer: null,
    error: '',
    isSetICE: false,
    subject: null,
    signaling: null,
    remoteVideoDom: null,
    localVideoDom: null,
    init: () => {
        var PeerConnection = window.RTCPeerConnection || window.mozRTCPeerConnection || window.webkitRTCPeerConnection;
        if (!PeerConnection) {
            this.error = '设备不支持RTC功能'
            return false;
        }
        if (!this.signaling) {
            this.error = 'signaling server not open'
            return false;
        }
        this.peer = new PeerConnection();
        this.peer.onicecandidate = (e) => {
            console.log(e)
            if (!e.candidate && this.isSetICE) {
                return this.handleAddCandidateError()
            }
            this.signaling.send(JSON.stringify({
                cmd: 'publish',
                subject: this.subject,
                event: 'candidate',
                data: e.candidate
            }))
            this.isSetICE = true
        }
        this.peer.ontrack = e => {
            if (e && e.streams) {
                this.remoteVideoDom.srcObject = e.streams[0];
            }
        };
    },
    handleAddCandidateError: () => {
        console.log("Oh noes! addICECandidate failed!");
    },
    startSignaling: (config) => {
        if (!config.ws || !config.subject) {
            return false;
        }
        this.localVideoDom = config.localVideoDom
        this.remoteVideoDom = config.remoteVideoDom
        var bool = true;
        var onopensocket = () => {
            this.signaling.send(JSON.stringify({
                cmd: 'subscribe',
                subject: config.subject
            }))
        }
        var onmessage = (mes) => {
            var res = this.evil(mes.data)
            switch (res.event) {
                case 'offer':
                    console.log(res.data)
                    this.answer(res.data)
                    break;
                case 'answer':
                    this.peer.setRemoteDescription(res.data)
                    console.log(res.data)
                    break;
                case 'candidate':
                    this.peer.addIceCandidate(res.data)
                    console.log(res.data)
                    break;
            }
        }
        var socketError = () =>{
            console.log('socket error')
            this.error = 'socket error'
            bool = false
        }
        var socketClose = () => {
            console.log('close socket')
            this.error = 'socket closed'
            bool = false
        }
        this.signaling = new WebSocket(config.ws)
        this.signaling.onopen = onopensocket
        this.signaling.onmessage = onmessage
        this.signaling.onerror = socketError
        this.signaling.onclose = socketClose
        return bool
    },
    offer: () => {
        this.peer.createOffer()
            .then(offer => this.peer.setLocalDescription(offer))
            .then(() => {
                //remoteConnection.setRemoteDescription(localConnection.localDescription)
                this.signaling.send(JSON.stringify({
                    cmd: 'publish',
                    subject: this.subject,
                    event: 'offer',
                    data: this.peer.localDescription
                }))
            }).catch(this.handleCreateDescriptionError);
    },
    answer: (localDescription) => {
        this.peer.setRemoteDescription(localDescription)
        this.peer.createAnswer()
            .then(answer => this.peer.setLocalDescription(answer))
            .then(() => {
                // localConnection.setRemoteDescription(remoteConnection.localDescription)
                this.signaling.send(JSON.stringify({
                    cmd: 'publish',
                    subject: this.subject,
                    event: 'answer',
                    data: this.peer.localDescription
                }))
            }).catch(this.handleCreateDescriptionError);
    },
    handleCreateDescriptionError: (error) => {
        console.log("Unable to create an offer: " + error.toString());
        this.error = error.toString()
    },
    evil: (fn) => {
        // 一个变量指向Function，防止有些前端编译工具报错
        let Fn = Function
        return new Fn('return ' + fn)()
    },
    startLive: async () => {
        let stream;
        try {
            stream = await (navigator.mediaDevices.getUserMedia({ video: true, audio: true }));
            this.localVideoDom.srcObject = stream;
        } catch (e) {
            this.error = e
            return false;
        }
        stream.getTracks().forEach(track => {
            this.peer.addTrack(track, stream);
        });
    }
}

