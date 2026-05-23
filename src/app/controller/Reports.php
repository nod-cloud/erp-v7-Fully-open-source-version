<?php
namespace app\controller ;
use app\controller\Acl;
use think\facade\Db;
use app\model\{Goods,Bor,BorInfo,Buy,BuyInfo,Bre,BreInfo,Sor,SorInfo,Sell,SellInfo,Sre,SreInfo,Entry,EntryInfo,Extry,ExtryInfo,Swap,SwapInfo,Vend,VendInfo,Vre,VreInfo,Barter,BarterInfo,Serve,ServeInfo,Imy,ImyInfo,Omy,OmyInfo,Ice,IceInfo,Oce,OceInfo,Allot,AllotInfo,Bill,BillInfo};
class Reports extends Acl {
    //系统参数
    public function sys(){
        $data = getSys();
        return json(['sys'=>$data]);
    }
    //商品标签数据|独立辅助属性
    public function goodsLabel(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            $data=[];
            empty($input['parm'])&&$input['parm']=array_column(Goods::limit(3)->field('id')->select()->toArray(),'id');
            $items=Goods::with(['attr'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $item) {
                if(empty($item['attr'])){
                    $item['attr']='';
                    $data[]=$item;
                }else{
                    foreach ($item['attr'] as $attr) {
                        $source=$item;
                        $source['attr']=$attr['name'];
                        empty($attr['code'])||$source['code']=$attr['code'];
                        $data[]=$source;
                    }
                }
            }
            $result=empty($data)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['goods'=>$data];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //采购订单
    public function bor(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Bor::limit(3)->field('id')->select()->toArray(),'id');
            $items=Bor::with(['frameData','supplierData','userData'])->where([['id','in',$input['parm']]])->order(['id'=>'desc'])->append(['extension'])->select()->toArray();
            foreach ($items as $key => $item) {
                $items[$key]['info']=BorInfo::with(['goodsData','warehouseData'])->where([['pid','=',$item['id']]])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['bor'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //采购单
    public function buy(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Buy::limit(3)->field('id')->select()->toArray(),'id');
            $items=Buy::with(['frameData','supplierData','userData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $key => $item) {
                $items[$key]['info']=BuyInfo::with(['goodsData','warehouseData'])->where([['pid','=',$item['id']]])->append(['extension'])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['buy'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //采购退货单
    public function bre(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Bre::limit(3)->field('id')->select()->toArray(),'id');
            $items=Bre::with(['frameData','supplierData','userData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $key => $item) {
                $items[$key]['info']=BreInfo::with(['goodsData','warehouseData'])->where([['pid','=',$item['id']]])->append(['extension'])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['bre'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //销售订单
    public function sor(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Sor::limit(3)->field('id')->select()->toArray(),'id');
            $items=Sor::with(['frameData','customerData','peopleData','userData'])->where([['id','in',$input['parm']]])->order(['id'=>'desc'])->append(['extension'])->select()->toArray();
            foreach ($items as $key => $item) {
                $items[$key]['info']=SorInfo::with(['goodsData','warehouseData'])->where([['pid','=',$item['id']]])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['sor'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //销售单
    public function sell(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Sell::limit(3)->field('id')->select()->toArray(),'id');
            $items=Sell::with(['frameData','customerData','peopleData','userData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $key => $item) {
                $items[$key]['info']=SellInfo::with(['goodsData','warehouseData'])->where([['pid','=',$item['id']]])->append(['extension'])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['sell'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //销售退货单
    public function sre(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Sre::limit(3)->field('id')->select()->toArray(),'id');
            $items=Sre::with(['frameData','customerData','peopleData','userData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $key => $item) {
                $items[$key]['info']=SreInfo::with(['goodsData','warehouseData'])->where([['pid','=',$item['id']]])->append(['extension'])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['sre'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //其它入库单
    public function entry(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Entry::limit(3)->field('id')->select()->toArray(),'id');
            $items=Entry::with(['frameData','userData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $key => $item) {
                $items[$key]['info']=EntryInfo::with(['goodsData','warehouseData'])->where([['pid','=',$item['id']]])->append(['extension'])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['entry'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //其它出库单
    public function extry(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Extry::limit(3)->field('id')->select()->toArray(),'id');
            $items=Extry::with(['frameData','userData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $key => $item) {
                $items[$key]['info']=ExtryInfo::with(['goodsData','warehouseData'])->where([['pid','=',$item['id']]])->append(['extension'])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['extry'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //调拨单
    public function swap(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Swap::limit(3)->field('id')->select()->toArray(),'id');
            $items=Swap::with(['frameData','userData','recordData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $key => $item) {
                $items[$key]['info']=SwapInfo::with(['goodsData','warehouseData','storehouseData'])->where([['pid','=',$item['id']]])->append(['extension'])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['swap'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //零售单
    public function vend(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Vend::limit(3)->field('id')->select()->toArray(),'id');
            $items=Vend::with(['frameData','customerData','peopleData','userData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $key => $item) {
                $items[$key]['info']=VendInfo::with(['goodsData','warehouseData'])->where([['pid','=',$item['id']]])->append(['extension'])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['vend'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //零售退货单
    public function vre(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Vre::limit(3)->field('id')->select()->toArray(),'id');
            $items=Vre::with(['frameData','customerData','peopleData','userData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $key => $item) {
                $items[$key]['info']=VreInfo::with(['goodsData','warehouseData'])->where([['pid','=',$item['id']]])->append(['extension'])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['vre'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //积分兑换单
    public function barter(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Barter::limit(3)->field('id')->select()->toArray(),'id');
            $items=Barter::with(['frameData','customerData','userData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $key => $item) {
                $items[$key]['info']=BarterInfo::with(['goodsData','warehouseData'])->where([['pid','=',$item['id']]])->append(['extension'])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['barter'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //服务单
    public function serve(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Serve::limit(3)->field('id')->select()->toArray(),'id');
            $items=Serve::with(['frameData','customerData','peopleData','userData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $key => $item) {
                $items[$key]['info']=ServeInfo::with(['goodsData','warehouseData'])->where([['pid','=',$item['id']]])->append(['extension'])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['serve'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //收款单
    public function imy(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Imy::limit(3)->field('id')->select()->toArray(),'id');
            $items=Imy::with(['frameData','customerData','peopleData','userData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $key => $item) {
                $items[$key]['info']=ImyInfo::with(['accountData'])->where([['pid','=',$item['id']]])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['imy'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //付款单
    public function omy(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Omy::limit(3)->field('id')->select()->toArray(),'id');
            $items=Omy::with(['frameData','supplierData','peopleData','userData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $key => $item) {
                $items[$key]['info']=OmyInfo::with(['accountData'])->where([['pid','=',$item['id']]])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['omy'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //其它收入单
    public function ice(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Ice::limit(3)->field('id')->select()->toArray(),'id');
            $items=Ice::with(['frameData','customerData','accountData','peopleData','userData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $key => $item) {
                empty($item['customerData'])&&$items[$key]['customerData']=['name'=>''];
                $items[$key]['info']=IceInfo::with(['ietData'])->where([['pid','=',$item['id']]])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['ice'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //其它支出单
    public function oce(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Oce::limit(3)->field('id')->select()->toArray(),'id');
            $items=Oce::with(['frameData','supplierData','accountData','peopleData','userData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $key => $item) {
                empty($item['supplierData'])&&$items[$key]['supplierData']=['name'=>''];
                $items[$key]['info']=OceInfo::with(['ietData'])->where([['pid','=',$item['id']]])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['oce'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //转账单
    public function allot(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Allot::limit(3)->field('id')->select()->toArray(),'id');
            $items=Allot::with(['frameData','peopleData','userData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $key => $item) {
                $items[$key]['info']=AllotInfo::with(['accountData','tatData'])->where([['pid','=',$item['id']]])->select()->toArray();
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['allot'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
    //核销单
    public function bill(){
        $input=input('post.');
        if(isset($input['parm']) && is_array($input['parm'])) {
            empty($input['parm'])&&$input['parm']=array_column(Bill::limit(3)->field('id')->select()->toArray(),'id');
            $items=Bill::with(['frameData','customerData','supplierData','userData'])->where([['id','in',$input['parm']]])->append(['extension'])->order(['id'=>'desc'])->select()->toArray();
            foreach ($items as $itemKey=>$item) {
                $info=BillInfo::with(['sourceData'])->where([['pid','=',$item['id']]])->append(['extension'])->select()->each(function($row){
                    $row->sourceData->append(['extension']);
                })->toArray();
                //数据处理
                foreach ($info as $infoKey=>$infoVo) {
                    in_array($infoVo['mold'],['buy','bre','sell','sre','ice','oce'])&&$info[$infoKey]['sourceData']['total']=$infoVo['sourceData']['actual'];
                    if(in_array($item['type'],[0,1,2]) && in_array($infoVo['mold'],['bre','sre'])){
                        $info[$infoKey]['sourceData']['total']*=-1;
                        $info[$infoKey]['sourceData']['extension']['amount']*=-1;
                        $info[$infoKey]['sourceData']['extension']['anwo']*=-1;
                    }
                }
                $items[$itemKey]['info']=$info;
            }
            $result=empty($items)?['state'=>'error','info'=>'[ ERROR ] 未匹配到数据!']:['bill'=>$items];
        }else{
            $result=['state'=>'error','info'=>'传入参数不完整!'];
        }
        return json($result);
    }
}
