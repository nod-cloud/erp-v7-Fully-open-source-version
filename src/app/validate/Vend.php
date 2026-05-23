<?php
namespace app\validate;
use think\Validate;
class Vend extends Validate {
    
    //常规验证规则
    protected $rule = [
        'customer' => ['require','integer'],
        'time' => ['require','date'],
        'number' => ['require','unique:vend'],
        'total' => ['require','float'],
        'actual' => ['require','float'],
        'integral' => ['float'],
        'account' => ['requireWith:money'],
        'ptm' => ['require'],
        'people' => ['integer'],
        'logistics' => ['array'],
        'file' => ['array'],
        'more' => ['array']
    ];

    //常规规则提示
    protected $message = [
        'customer.require' => '客户不可为空!',
        'customer.integer' => '客户不正确!',
        'time.require' => '单据日期不可为空!',
        'time.date' => '单据日期不正确!',
        'number.require' => '单据编号不可为空!',
        'number.unique' => '单据编号重复!',
        'total.require' => '单据金额不可为空!',
        'total.float' => '单据金额不正确!',
        'actual.require' => '实际金额不可为空!',
        'actual.float' => '实际金额不正确!',
        'integral.float' => '单据积分不正确!',
        'account.requireWith' => '结算账户不可为空!',
        'ptm.require' => '结算方式不可为空!',
        'people.integer' => '关联人员不正确!',
        'logistics.array' => '物流信息不正确!',
        'file.array' => '单据附件不正确!',
        'more.array' => '扩展信息不正确!'
    ];

    //场景规则
    protected $scene = [
        'update' => ['customer','time','number','total','actual','integral','account','ptm','people','logistics','file','more']
    ];
}