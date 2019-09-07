import request from '@/utils/request'

/**
 * 产品分类销量分析
 */
export function getAccountDatnum(data) {
    return request({
        url: 'bi/contract/accounts',
        method: 'post',
        data: data
    })
}
