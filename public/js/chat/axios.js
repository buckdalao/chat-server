var http = axios.create({
    baseURL: window.location.protocol + '//' + window.location.host,
    timeout: 1000 * 60,
    headers: {
        'Accept': 'application/prs.chat.v1+json'
    }
})
// 添加请求拦截器
http.interceptors.request.use(function (config) {
    // 在发送请求之前做些什么
    if (localStorage.getItem('token')) {
        config.headers.Authorization = localStorage.getItem('tokenType') + ' ' + localStorage.getItem('token')
    }
    config.headers.common['Accept-Language'] = 'zh-CN'
    //config.headers.common['X-CSRF-TOKEN'] = csrf_token
    let t = (new Date().getTime()) / 1000
    t = parseInt(t);
    let r = Math.floor(Math.random()*10000000);
    let key = ''
    var sr = 'u=' + appId +'&k=' + secretId + '&t=' + t + '&r=' + r + '&f=';
    key = CryptoJS.HmacSHA1(sr, secretKey);
    let salt = t + ';' + r + ';' + secretId
    config.headers.common['Client-Key'] = window.btoa(key + sr)
    config.headers.common['Secret-Salt'] = salt
    iview.LoadingBar.start()
    return config
}, function (error) {
    // 对请求错误做些什么
    iview.LoadingBar.error()
    return Promise.reject(error)
})

// 添加响应拦截器
http.interceptors.response.use(function (response) {
    // 对响应数据做点什么
    iview.LoadingBar.finish()
    return response
}, function (error) {
    console.log(error)
    // 对响应错误做点什么
    iview.LoadingBar.error()
    if (error.response.status === 401 || error.response.data.status_code === 401) {
        http.post('/auth/logout').then(() => {
            localStorage.clear()
            location.href = '/auth/login'
        })
    }
    if (error.response.status === 419 || error.response.data.status_code === 419) {
        location.reload()
    }
    return Promise.reject(error)
})
