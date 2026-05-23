<?php
namespace app\model;
use	think\Model;
class Vend extends Model{
    //零售单
    protected $type = [
        'time'=>'timestamp:Y-m-d',
        'logistics'=>'json',
        'file'=>'json'
    ];
    
    //所属组织关联
    public function frameData(){
        return $this->hasOne(Frame::class,'id','frame');
    }
    
    //客户关联
    public function customerData(){
        return $this->hasOne(Customer::class,'id','customer')->append(['extension']);
    }
    
    //结算账户关联
    public function accountData(){
        return $this->hasOne(Account::class,'id','account');
    }
    
    //关联人员关联
    public function peopleData(){
        return $this->hasOne(People::class,'id','people')->field(['id','name']);
    }
	
    //制单人关联
    public function userData(){
        return $this->hasOne(User::class,'id','user')->field(['id','name']);
    }
    
    //费用详情关联
    public function costData(){
        return $this->hasMany(Cost::class,'class','id')->with(['ietData'])->where([['type','=','vend']])->append(['extension'])->order('id desc');
    }
    
    //发票关联
    public function invoiceData(){
        return $this->hasMany(Invoice::class,'class','id')->where([['type','=','vend']])->order('id desc');
    }
    
    //记录关联
    public function recordData(){
        return $this->hasMany(Record::class,'source','id')->with(['userData'])->where([['type','=','vend']])->append(['extension'])->order('id desc');
    }
    
    //单据金额_读取器
	public function getTotalAttr($val,$data){
        return floatval($val);
	}
	
	//实际金额_读取器
	public function getActualAttr($val,$data){
        return floatval($val);
	}
	
	//单据积分_读取器
	public function getIntegralAttr($val,$data){
        return floatval($val);
	}
	
	//单据费用_读取器
	public function getCostAttr($val,$data){
        return floatval($val);
	}
	
    //结算账户_设置器
    public function setAccountAttr($val,$data){
        return empty($val)?0:$val;
    }
    
    //结算账户_读取器
    public function getAccountAttr($val,$data){
        return empty($val)?null:$val;
    }
    
    //关联人员_设置器
	public function  setPeopleAttr($val){
	    return empty($val)?0:$val;
	}
	
	//关联人员_读取器
	public function  getPeopleAttr($val){
		return empty($val)?null:$val;
	}
    
	//扩展信息_设置器
	public function  setMoreAttr($val){
	    //兼容Api|修复PHP空对象json编码为[]
	    return json_encode((object)$val);
	}
	
	//扩展信息_读取器
	public function  getMoreAttr($val){
		return json_decode($val);
	}
	
	//数据扩展
	public function getExtensionAttr($val,$data){
        $source=[];
        //客户积分
        $deploy=getFrameDeploy();
        $customer=db('customer')->where([['id','=',$data['customer']]])->find();
        if(empty($deploy)){
            $source['crIntegral']=floatval($customer['integral']);
        }else{
            //默认客户积分为0
            if($deploy['base']['customer']==$data['customer']){
                $source['crIntegral']=0;
            }else{
                $source['crIntegral']=floatval($customer['integral']);
            }
        }
        //结算方式
        if(in_array($data['ptm'],['cash','wechat','ali'])){
            $source['ptm']=['cash'=>'现金','wechat'=>'微信','ali'=>'支付宝'][$data['ptm']];
        }else{
            $deploy=getFrameDeploy();
            if(empty($deploy)){
                $source['ptm']=$data['ptm'];
            }else{
                $find=search($deploy['other'])->where([['key','=',$data['ptm']]])->find();
                if(empty($find)){
                    $source['ptm']='未知';
                }else{
                    $source['ptm']=$find['name'];
                }
            }
        }
        //物流信息
        $logistics=json_decode($data['logistics'],true);
        if(empty($logistics['key'])){
            $source['logistics']='';
        }elseif($logistics['key']=='auto'){
            $source['logistics']=$logistics['number'];
        }else{
            $source['logistics']=$logistics['name'].'|'.$logistics['number'];
        }
        //审核状态
        $source['examine']=[0=>'未审核',1=>'已审核'][$data['examine']];
        //费用状态
        $source['cse']=[0=>'未结算',1=>'部分结算',2=>'已结算',3=>'无需结算'][$data['cse']];
        //发票状态
        $source['invoice']=[0=>'未开具',1=>'部分开具',2=>'已开具',3=>'无需开具'][$data['invoice']];
        //核对状态
        $source['check']=[0=>'未核对',1=>'已核对'][$data['check']];
        return $source;
	}
	
	//EVENT|更新前
    public static function onBeforeUpdate($model){
        $source=$model::where([['id','=',$model['id']]])->find();
        if(!empty($source['examine'])){
            exit(json(['state'=>'error','info'=>'[ ERROR ] 单据已审核!'],200)->send());
        }
    }
}
