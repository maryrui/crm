import request from '@/utils/request'

// 获取客诉列表
export function getComplaintList(data) {
    return request({
        url: 'admin/complaint/type/index',
        method: 'get'
       /* data: data,
        headers: {
            'Content-Type': 'multipart/form-data'
        }*/
    })
}

// 保存客诉类型
export function saveComplaintType(data) {
    return request({
        url: 'admin/complaint/type/save',
        method: 'post',
        data: data
      /*  headers: {
            'Content-Type': 'multipart/form-data'
        }*/
    })
}
