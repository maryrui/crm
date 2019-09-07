import request from '@/utils/request'

// 获取投诉列表
export function getComplaintList(data) {
    return request({
        url: 'crm/complaint/index',
        method: 'post',
        data: data
    })
}

// 状态改变
export function updateComplaintOne(data) {
    return request({
        url: 'crm/complaint/update',
        method: 'post',
        data: data
    })
}

// 状态改变
export function checkComplaintOne(data) {
    return request({
        url: 'crm/complaint/check',
        method: 'post',
        data: data
    })
}

// 新建客诉
export function saveComplaint(data) {
    return request({
        url: 'extend/wechat/complaint/save',
        method: 'post',
        data: data
    })
}
// 获取客诉类型
export function getComplaintType() {
    return request({
        url: 'extend/wechat/complaint/types',
        method: 'get'
    })
}
