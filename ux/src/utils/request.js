import axios from 'axios'
import {
  Message,
  MessageBox
} from 'element-ui'
import {
  removeAuth
} from '@/utils/auth'
import qs from 'qs'
var showLoginMessageBox = false
axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded;charset=UTF-8'
// 创建axios实例
import { baseUrl } from "./env"

// console.log(baseURL);
const service = axios.create({
  // baseURL:baseURL +'index.php/', // api 的 base_url
  baseURL: baseUrl, // api 的 base_url
  timeout: 15000 // 请求超时时间
})

// request拦截器
service.interceptors.request.use(
  config => {
    if (!config.headers['Content-Type'] || config.headers['Content-Type'].indexOf('multipart/form-data') == -1) {
      config.data = qs.stringify(config.data)
    }
    return config
  },
  error => {
    // Do something with request error
    Promise.reject(error)
  }
)

// response 拦截器
service.interceptors.response.use(
  response => {
    /**
     * code为非200是抛错
     */
    const res = response.data
    if (response.status === 200 && response.config.responseType === 'blob') { // 文件类型特殊处理
      return response
    } else if (res.code !== 200) {
      // 101	登录已失效 102	没有权限 103	账号已被删除或禁用
      if (res.code === 101) {
        if (!showLoginMessageBox) {
          showLoginMessageBox = true
          MessageBox.confirm(
            '你已被登出，请重新登录',
            '确定登出', {
              showCancelButton: false,
              showClose: false,
              confirmButtonText: '重新登录',
              type: 'warning'
            }
          ).then(() => {
            showLoginMessageBox = false
            removeAuth().then(() => {
              location.reload() // 为了重新实例化vue-router对象 避免bug
            }).catch(() => {
              location.reload()
            })
          })
        }
      } else if (res.code === 402) {
        if (res.error && Object.prototype.toString.call(res.error) === '[object Array]') {
          res.error = res.error.reduce(function (prev, cur, index, array) {
            return prev + "\r\n" + cur
          })
        }
        Message({
          showClose: true,
          duration: 0,
          customClass: 'el-close-message',
          message: res.error,
          type: 'error'
        })
      } else {
        if (res.error) {
          Message({
            message: res.error,
            type: 'error'
          })
        }
      }
      return Promise.reject(res.error)
    } else {
      return res
    }
  },
  error => {
    Message({
      message: '网络请求失败，请稍候再试',
      type: 'error'
    })
    return Promise.reject(error)
  }
)

export default service
