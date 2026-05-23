<?php
namespace app\validate;
use think\Validate;
class BarterInfo extends Validate {
    
    //常规验证规则
    protected $rule = [
        'goods' => ['require','integer'],
        'unit' => ['require'],
        'warehouse' => ['integer'],
        'mfd' => ['date'],
        'price' => ['require','float'],
        'nums' => ['require','float'],
        'serial' => ['array'],
        'discount' => ['require','float'],
        'dsc' => ['require','float'],
        'total' => ['require','float'],
        'integral' => ['require','float'],
    ];

    //常规规则提示
    protected $message = [
        'goods.require' => '商品信息不可为空!',
        'goods.integer' => '商品信息不正确!',
        'unit.require' => '单位不可为空!',
        'warehouse.integer' => '仓库不正确!',
        'mfd.date' => '生产日期不正确!',
        'price.require' => '单价不可为空!',
        'price.float' => '单价不正确!',
        'nums.require' => '数量不可为空!',
        'nums.float' => '数量不正确!',
        'serial.array' => '序列号不正确!',
        'discount.require' => '折扣率不可为空!',
        'discount.float' => '折扣率不正确!',
        'dsc.require' => '折扣额不可为空!',
        'dsc.float' => '折扣额不正确!',
        'total.require' => '金额不可为空!',
        'total.float' => '金额不正确!',
        'integral.require' => '积分不可为空!',
        'integral.float' => '积分不正确!'
    ];
}