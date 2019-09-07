import request from '@/utils/request'

/**
 * 产品分类销量分析
 */
export function getComplaintDatnum(data) {
    return request({
        url: 'bi/customer/complaint',
        method: 'post',
        data: data
    })
}
