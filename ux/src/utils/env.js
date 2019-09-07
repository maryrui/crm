/**
 * 配置编译环境和线上环境之间的切换
 *
 * baseUrl: 域名地址
 * routerMode: 路由模式
 * imgBaseUrl: 图片所在域名地址
 *
 */

let baseUrl = ''
// let routerMode = 'history';
let hrefs = []
if (window.location.href.indexOf("index.html") != -1) {
    hrefs = window.location.href.split('index.html')
} else {
    hrefs = window.location.href.split('#')
}
//默认请求地址
let baseURL = hrefs.length > 0 ? hrefs[0] : window.location.href
// baseURL +'index.php/'
// process.env.BASE_API 自定义请求地址

if (process.env.NODE_ENV == 'development') {
        // baseUrl="http://192.168.0.221";
    baseUrl = process.env.BASE_API + '/'
}else if (process.env.NODE_ENV == 'production') {

    baseUrl = baseURL + 'index.php/'
}

export {
    baseUrl
}