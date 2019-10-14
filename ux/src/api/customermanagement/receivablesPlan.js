import request from '@/utils/request'

// crm 新建产品
export function crmReceivablesPlanDelete(data) {
    return request({
      url: 'crm/receivables_plan/delete',
      method: 'post',
      data: data
    })
}

// 审核回款
export function crmReceivablesPlanCheck(data) {
    return request({
        url: 'crm/receivables_plan/check',
        method: 'post',
        data: data
    })
}
