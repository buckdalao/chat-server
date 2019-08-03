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
    config.headers.common['Client-Key'] = localStorage.getItem('clientKey') ? localStorage.getItem('clientKey') : ''
    return config
}, function (error) {
    // 对请求错误做些什么
    return Promise.reject(error)
})

// 添加响应拦截器
http.interceptors.response.use(function (response) {
    // 对响应数据做点什么
    return response
}, function (error) {
    // 对响应错误做点什么
    if (error.response.status === 401 || error.response.data.status_code === 401) {
        http.post('/auth/logout').then(() => {
            localStorage.clear()
            location.href = '/auth/login'
        })
    }
    return Promise.reject(error)
})
