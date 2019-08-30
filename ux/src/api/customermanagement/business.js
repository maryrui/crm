import request from '@/utils/request'

// crm 新建合同
export function crmBusinessSave(data) {
  return request({
    url: 'crm/business/save',
    method: 'post',
    data: data
  })
}

// crm 列表
export function crmBusinessIndex(data) {
  return request({
    url: 'crm/business/index',
    method: 'post',
    data: data
  })
}

// 删除
export function crmBusinessDelete(data) {
  return request({
    url: 'crm/business/delete',
    method: 'post',
    data: data
  })
}

// crm 更新
export function crmBusinessUpdate(data) {
  return request({
    url: 'crm/business/update',
    method: 'post',
    data: data
  })
}

// crm 合同状态组
export function crmBusinessStatusList(data) {
  return request({
    url: 'crm/business/statusList',
    method: 'post',
    data: data
  })
}

// crm 详情
export function crmBusinessRead(data) {
  return request({
    url: 'crm/business/read',
    method: 'post',
    data: data
  })
}

/**
 * 合同转移
 * @param {*} data
 * business_id 	合同数组
 * owner_user_id 	变更负责人
 * is_remove 1移出，2转为团队成员
 * type 权限 1只读2读写
 */
export function crmBusinessTransfer(data) {
  return request({
    url: 'crm/business/transfer',
    method: 'post',
    data: data
  })
}

/**
 * 合同转移
 * @param {*} data
 * business_id 	合同
 * status_id 	合同状态ID
 * content 备注
 */
export function crmBusinessAdvance(data) {
  return request({
    url: 'crm/business/advance',
    method: 'post',
    data: data
  })
}

/**
 * 合同相关产品
 * @param {*} data
 * business_id 	合同ID
 */
export function crmBusinessProduct(data) {
  return request({
    url: 'crm/business/product',
    method: 'post',
    data: data
  })
}
