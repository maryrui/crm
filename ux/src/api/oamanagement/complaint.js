import request from '@/utils/request'

// 获取投诉列表 auditComplaintList
export function getComplaintList(data) {
    return request({
        url: 'crm/complaint/index',
        method: 'post',
        data: data
    })
}
// 获取待审核客诉列表
export function auditComplaintList(data) {
    return request({
        url: 'crm/message/checkcomplaint',
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
        url: 'crm/complaint/save',
        method: 'post',
        data: data
    })
}
// 编辑客诉
export function updateComplaint(data) {
    return request({
        url: 'crm/complaint/update',
        method: 'post',
        data: data
    })
}
// 获取客诉类型
export function getComplaintType() {
    return request({
        url: 'admin/complaint/type/index',
        method: 'get'
    })
}

// 获取部门员工树
export function getComplaintUserTree() {
    return request({
        url: 'admin/users/tree',
        method: 'post'
    })
}
