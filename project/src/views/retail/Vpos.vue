<template>
	<div class="vpos" @click="vposClick">
		<el-container class="container">
			<el-header>
				<el-row>
					<el-col :span="4">
						<span>{{deploy.base.title}}</span>
					</el-col>
					<el-col :span="20">
						<ul class="headerList">
							<li>
								<nodList v-model="panel.form.customer" ref="customerList" class="headerCustomer" placeholder="F12 选择客户" action="service/customerRecord" scene="customer" @change="customerChange"></nodList>
							</li>
							<li><i class="el-icon-full-screen" @click="fullScreenTip"></i></li>
						</ul>
					</el-col>
				</el-row>
			</el-header>
			<el-container class="content">
				<el-aside>
					<el-card class="contentCard tableCard" tabindex="-1" @keydown.native="tableCardEvent">
						<div slot="header">
							<span>F4 商品信息</span>
							<i class="el-icon-delete clearTable" @click="clearTable"></i>
						</div>
						<div class="goodsTable">
							<el-table :data="table.data" @row-click="tableRowClick" height="calc(100% - 30px)">
								<el-table-column prop="name" label="商品名称" align="center" min-width="150px">
									<template slot-scope="scope">
										<span>{{scope.row.name}}{{scope.row.attr==''?'':' - '+scope.row.attr}}</span>
									</template>
								</el-table-column>
								<el-table-column prop="nums" label="数量" align="center" min-width="60px"></el-table-column>
								<el-table-column prop="tpt" label="金额" align="center" min-width="60px"></el-table-column>
								<el-table-column prop="set" label="操作" align="center" min-width="50px">
									<template slot-scope="scope">
										<i class="el-icon-delete" @click.stop="delTable(scope.$index)"></i>
									</template>
								</el-table-column>
							</el-table>
						</div>
						<el-popover v-model="table.popover" class="formPopover" popper-class="vposFormPopover" trigger="manual" placement="right-start">
							<div slot="reference">
								<i class="el-icon-caret-right ico"></i>
							</div>
							<template v-if="table.popover">
								<div class="form" tabindex="-1" @keydown.esc="formEscEvent">
									<p class="title">商品参数</p>
									<el-form :model="table.data[table.focus]" :rules="form.rules" label-width="80px">
										<el-form-item label="商品名称">
											<el-input v-model="table.data[table.focus].name" disabled></el-input>
										</el-form-item>
										<template v-if="table.data[table.focus].attr!=''">
											<el-form-item label="辅助属性">
												<el-input v-model="table.data[table.focus].attr" disabled></el-input>
											</el-form-item>
										</template>
										<template v-if="table.data[table.focus].unitData.length>0">
											<el-form-item label="单位" prop="unit">
												<el-select v-model="table.data[table.focus].unit" @change="unitChange" placeholder="请选择单位">
													<template v-for="unit in [{name:table.data[table.focus].unitData[0].source}].concat(table.data[table.focus].unitData)">
														<el-option :label="unit.name" :value="unit.name"></el-option>
													</template>
												</el-select>
											</el-form-item>
										</template>
										<template v-if="table.data[table.focus].goodsType==0">
											<el-form-item label="仓库" prop="warehouse">
												<el-select v-model="table.data[table.focus].warehouse" @change="handleTable" placeholder="请选择仓库">
													<template slot="prefix">
														<i class="el-icon-search" @click.stop="showStock"></i>
													</template>
													<template v-for="warehouse in this.store.warehouse">
														<el-option :label="warehouse.name" :value="warehouse.id"></el-option>
													</template>
												</el-select>
												<Stock v-if="form.stock" :condition="form.stockCondition" @rowClick="stockRowClick" @destroy="stockClose"></Stock>
											</el-form-item>
										</template>
										<template v-if="table.data[table.focus].batchType">
											<el-form-item label="批次号" prop="batch">
												<el-input v-model="table.data[table.focus].batch" @change="handleTable" placeholder="请输入批次号" clearable>
													<template slot="append">
														<span @click="showLot">BP</span>
													</template>
												</el-input>
											</el-form-item>
											<template v-if="table.data[table.focus].validityType">
												<el-form-item label="生产日期" prop="mfd" clearable>
													<el-date-picker type="date" v-model="table.data[table.focus].mfd" @change="handleTable" placeholder="请选择生产日期"></el-date-picker>
												</el-form-item>
											</template>
											<Lot v-if="form.lot" :condition="form.lotCondition" @rowClick="lotRowClick" @destroy="lotClose"></Lot>
										</template>
										<el-form-item label="单价" prop="price">
											<el-input v-model="table.data[table.focus].price" @change="handleTable" placeholder="请输入单价" clearable></el-input>
										</el-form-item>
										<el-form-item label="数量" prop="nums">
											<template v-if="table.data[table.focus].serialType">
												<el-input v-model="table.data[table.focus].nums" disabled>
													<template slot="append">
														<span @click="showSnu">SN</span>
													</template>
												</el-input>
												<Snu v-if="form.snu" :config="form.snuConfig" @save="saveSnu" @destroy="snuClose"></Snu>
											</template>
											<template v-else>
												<el-input v-model="table.data[table.focus].nums" @change="handleTable" placeholder="请输入数量" clearable></el-input>
											</template>
										</el-form-item>
										<el-form-item label="折扣率" prop="discount">
											<el-input v-model="table.data[table.focus].discount" @change="handleTable" placeholder="请输入折扣率" clearable>
												<template slot="append">%</template>
											</el-input>
										</el-form-item>
										<el-form-item label="折扣额">
											<el-input v-model="table.data[table.focus].dsc" disabled></el-input>
										</el-form-item>
										<el-form-item label="总金额">
											<el-input v-model="table.data[table.focus].total" disabled></el-input>
										</el-form-item>
										<template v-if="store.sys.fun.tax">
											<el-form-item label="税率" prop="tax">
												<el-input v-model="table.data[table.focus].tax" @change="handleTable" placeholder="请输入税率" clearable>
													<template slot="append">%</template>
												</el-input>
											</el-form-item>
											<el-form-item label="税额">
												<el-input v-model="table.data[table.focus].tat" disabled></el-input>
											</el-form-item>
											<el-form-item label="价税合计">
												<el-input v-model="table.data[table.focus].tpt" disabled></el-input>
											</el-form-item>
										</template>
										<el-form-item label="备注信息">
											<el-input v-model="table.data[table.focus].data" placeholder="请输入备注信息" clearable></el-input>
										</el-form-item>
									</el-form>
								</div>
							</template>
						</el-popover>
						<div class="cardFooter">
							<el-row class="tableCount">
								<el-col :span="16"><p>合计 {{table.data.length}} 项</p></el-col>
								<el-col :span="8"><span>{{table.total}} 元</span></el-col>
							</el-row>
						</div>
					</el-card>
				</el-aside>
				<el-main>
					<el-card class="contentCard listCard" tabindex="-1" @keydown.native="listEvent">
						<div slot="header">
							<el-row>
								<el-col :span="6">
									<span>F3 商品列表</span>
								</el-col>
								<el-col :span="18">
									<el-input class="goodsInput" v-model="search.value" @keydown.native="goodsInputEvent" placeholder="F1 输入内容" size="mini" clearable>
										<el-select slot="prepend" class="goodsInputModel" v-model="search.model" @change="modelFocus">
											<el-option label="常规" value="mate"></el-option>
											<el-option label="扫码" value="code"></el-option>
										</el-select>
										<el-button slot="append" icon="el-icon-search" @click="record(1)"></el-button>
									</el-input>
								</el-col>
							</el-row>
						</div>
						<div class="goodsList" @keydown.stop>
							<template v-if="list.data.length==0">
								<div class="listEmpty">
									<i class="el-icon-warning-outline"></i>
									<p>未匹配到商品数据!</p>
								</div>
							</template>
							<template v-else>
								<el-row :gutter="12">
									<template v-for="(goods,index) in list.data">
										<el-col class="listCol" :xs="24" :sm="12" :md="8" :lg="6" :xl="4">
											<el-card :class="[{goodsCardFocus:list.focus==index},'goodsCard']" @click.native.stop="goodsClick(goods,index)">
												<template v-if="goods.attr.length>0">
													<div class="goodsMoreTip">
														<span>more</span>
													</div>
												</template>
												<template v-if="list.more==goods.id">
													<div class="goodsMore" tabindex="-1" @keydown.stop.prevent="goodsMoreEvent">
														<p class="title">{{goods.name}}</p>
														<ul class="attrList">
															<template v-for="(attr,attrIndex) in goods.attr">
																<li :class="[{attrListFocus:list.attr==attrIndex}]" @click.stop="attrClick(attr,attrIndex)">
																	<el-row class="info">
																		<el-col :span="16"><p>{{attr.name}}</p></el-col>
																		<el-col :span="8"><span>{{attr.retail}} 元</span></el-col>
																	</el-row>
																</li>
															</template>
														</ul>
													</div>
												</template>
												<template v-else>
													<div class="goodsRoutine">
														<template v-if="goods.imgs.length>0">
															<img class="goodsImg" :src="goods.imgs[0].url">
														</template>
														<template v-else>
															<div class="goodsImgSlot">
																<span>{{goods.name}}</span>
															</div>
														</template>
														<el-row class="info">
															<el-col :span="16"><p>{{goods.name}}</p></el-col>
															<el-col :span="8"><span>{{goods.retail}} 元</span></el-col>
														</el-row>
													</div>
												</template>
											</el-card>
										</el-col>
									</template>
								</el-row>
							</template>
						</div>
						<div class="cardFooter">
							<el-pagination :current-page.sync="page.current" :total="page.total" :page-size.sync="page.size" :page-sizes="page.sizes" :pager-count="page.count" @size-change="record(1)" @current-change="record(0)" layout="prev,pager,next,jumper,sizes,total"></el-pagination>
						</div>
					</el-card>
				</el-main>
			</el-container>
			<el-footer>
				<el-row>
					<el-col :span="20">
						<el-button-group>
							<el-button type="info" size="medium" @click="pend">F7 挂单</el-button>
							<el-popover class="btnGroupPopover" type="right" ref="pendPopper" popper-class="listPopover" placement="top" @show="pendPopperShow">
								<el-button type="info" size="medium" slot="reference">F8 源单</el-button>
								<template v-if="memory.data.length>0">
									<ul class="pendList" tabindex="-1" @keydown.stop.prevent="pendEvent">
										<template v-for="(item,index) in memory.data">
											<li :class="{pendFocus:index==memory.focus}">
												<span @click="restore(index)">No.{{index+1}} - {{item.time}}</span>
												<i class="el-icon-delete" @click="delPend(index)"></i>
											</li>
										</template>
									</ul>
								</template>
								<template v-else>
									<p class="emptyPend">暂无源单</p>
								</template>
							</el-popover>
						</el-button-group>
					</el-col>
					<el-col :span="4" style="text-align: right;">
						<el-button type="primary" size="medium" @click="settle">F9 结账</el-button>
					</el-col>
				</el-row>
			</el-footer>
		</el-container>
		<el-dialog :visible.sync="panel.dialog" ref="panelDialog" title="结账" width="660px" tabindex="-1" @keydown.native.stop="panelEvent" @closed="panelClose" :close-on-click-modal="false" v-madeDialog>
			<transition name="el-fade-in">
				<template v-if="panel.dialog">
					<div class="panel">
						<template v-if="panel.payment.state==2">
							<el-row :gutter="12">
								<el-col :md="8">
									<div class="mouth"></div>
									<div class="paper">
										<p class="title">{{panel.form.number}}</p>
										<hr>
										<p class="distribute"><span>单据金额</span><span>{{panel.form.total}}</span></p>
										<p class="distribute"><span>实际金额</span><span>{{panel.form.actual}}</span></p>
										<p class="distribute"><span>单据积分</span><span>{{panel.form.integral}}</span></p>
										<p class="distribute"><span>付款方式</span><span>{{paymentName}}</span></p>
										<p class="distribute"><span>收款金额</span><span>{{panel.form.ptm=='cash'?panel.payment.money:panel.form.actual}}</span></p>
										<p class="distribute"><span>找零金额</span><span style="color: #FF0000;">{{panel.form.ptm=='cash'?panel.payment.give:0}}</span></p>
									</div>
								</el-col>
								<el-col :md="16">
									<el-card class="finishCard">
										<div class="finish">
											<i class="el-icon-check"></i>
											<p>收款成功</p>
											<div class="operate">
												<el-button type="primary" @click="print(false)">F10 打印小票</el-button>
												<el-button type="success" @click="reduction">ESC 完成收银</el-button>
											</div>
										</div>
									</el-card>
								</el-col>
							</el-row>
						</template>
						<template v-else>
							<el-row :gutter="12">
								<el-col :md="8">
									<el-card>
										<div slot="header">
											<span>单据信息</span>
										</div>
										<el-form label-width="70px">
											<el-form-item label="单据金额">
												<el-input v-model="panel.form.total" disabled></el-input>
											</el-form-item>
											<el-form-item label="实际金额">
												<el-input v-model="panel.form.actual" @input="actualChange"></el-input>
											</el-form-item>
											<el-form-item label="单据积分">
												<el-input v-model="panel.form.integral"></el-input>
											</el-form-item>
											<el-form-item label="结算账户">
												<nodList v-model="panel.form.account" placeholder="结算账户" action="service/accountRecord" scene="account"></nodList>
											</el-form-item>
											<el-form-item label="备注信息">
												<el-input v-model="panel.form.data"></el-input>
											</el-form-item>
										</el-form>
									</el-card>
								</el-col>
								<el-col :md="16">
									<el-tabs class="paymentTabs" v-model="panel.active" @tab-click="paymentTabsClick" type="border-card" tab-position="right">
										<el-tab-pane label="1 现金" name="cash">
											<ul class="cashList">
												<li>
													<span>应 收</span>
													<p>{{panel.form.actual}}</p>
												</li>
												<li>
													<span>收 款</span>
													<p><input class="paymentMoney" type="text" v-model="panel.payment.money" @input="paymentMoneyChange" @keydown.enter="final"></p>
												</li>
												<li>
													<span>找 零</span>
													<p style="color: #f00;">{{panel.payment.give}}</p>
												</li>
											</ul>
											<div class="board">
												<ul>
													<li @click="board('1')">1</li>
													<li @click="board('2')">2</li>
													<li @click="board('3')">3</li>
													<li @click="board('del')"><i class="el-icon-back"></i></li>
													<li @click="board('4')">4</li>
													<li @click="board('5')">5</li>
													<li @click="board('6')">6</li>
													<li @click="board('empty')"><i class="el-icon-delete"></i></li>
													<li @click="board('7')">7</li>
													<li @click="board('8')">8</li>
													<li @click="board('9')">9</li>
													<li @click="board('00')">00</li>
													<li @click="board('0')">0</li>
													<li @click="board('.')">.</li>
													<li @click="board('final')" class="stress">F9 结 算</li>
												</ul>
											</div>
										</el-tab-pane>
										<template v-if="deploy.wechat.enable">
											<el-tab-pane label="2 微信" name="wechat" >
												<div class="scan">
													<i class="el-icon-connection"></i>
													<span>请扫描顾客付款码</span>
													<el-input v-model="panel.payment.wechat.code" ref="paymentWechat" @keydown.enter.native="final" placeholder="..." clearable>
														<template slot="append">
															<span @click="final">{{panel.form.ptn==''?'收款':'完成'}}</span>
														</template>
														<template v-if="panel.form.ptm=='wechat' && panel.form.ptn!=''" slot="prepend">
															<el-popconfirm title="请再次确定是否退款?" @onConfirm="scanPay('wechat','cancel')">
																<span slot="reference">退款</span>
															</el-popconfirm>
														</template>
													</el-input>
													<template v-if="panel.payment.wechat.tip!=''">
														<p>{{panel.payment.wechat.tip}}</p>
													</template>
												</div>
											</el-tab-pane>
										</template>
										<template v-if="deploy.ali.enable">
											<el-tab-pane label="3 支付宝" name="ali">
												<div class="scan">
													<i class="el-icon-connection"></i>
													<span>请扫描顾客付款码</span>
													<el-input v-model="panel.payment.ali.code" ref="paymentAli" @keydown.enter.native="final" placeholder="..." clearable>
														<template slot="append">
															<span @click="final">{{panel.form.ptn==''?'收款':'完成'}}</span>
														</template>
														<template v-if="panel.form.ptm=='ali' && panel.form.ptn!=''" slot="prepend">
															<el-popconfirm title="请再次确定是否退款?" @onConfirm="scanPay('ali','cancel')">
																<span slot="reference">退款</span>
															</el-popconfirm>
														</template>
													</el-input>
													<template v-if="panel.payment.ali.tip!=''">
														<p>{{panel.payment.ali.tip}}</p>
													</template>
												</div>
											</el-tab-pane>
										</template>
										<template v-for="(other,index) in deploy.other">
											<el-tab-pane :label="(index+4)+' '+other.name" :name="other.key">
												<div class="scan">
													<i class="el-icon-connection"></i>
													<span>请扫描三方结算单号</span>
													<el-input v-model="panel.payment.other[other.key]" :ref="'payment'+other.key" @keydown.enter.native="final" placeholder="..." clearable>
														<template slot="append">
															<span @click="final">完成</span>
														</template>
													</el-input>
												</div>
											</el-tab-pane>
										</template>
									</el-tabs>
									<span class="paymentTip">ALT+序号</span>
								</el-col>
							</el-row>
						</template>
					</div>
				</template>
			</transition>
		</el-dialog>
		<div v-if="table.popover" class="vposModal" @click="table.popover=false"></div>
		<Viewer v-if="report.dialog" mould="vend" :auto='report.auto' :source="report.source"  @startPrint="startPrint" @destroy="viewerDestroy"></Viewer>
	</div>
</template>
<script>
	import NodList from "@/components/lib/NodList";
	import Stock from "@/components/dialog/Stock";
	import Lot from "@/components/dialog/Lot";
	import Snu from "@/components/dialog/Snu";
	import Viewer from "@/components/report/Viewer";
	export default {
		name: "Vpos",
		components: {
			NodList,
			Stock,
			Lot,
			Snu,
			Viewer
		},
		data() {
			return {
				deploy:{
					base:{title:"零售终端"},
					ali:{enable:false},
					wechat:{enable:false}
				},
				customer:{
					grade:""
				},
				search: {
					value: "",
					model: "mate"
				},
				table:{
					data:[],
					focus:-1,
					popover:false,
					total:0
				},
				form:{
					stock:false,
					stockCondition:{},
					lot:false,
					lotCondition:{},
					snu:false,
					snuConfig:{},
					rules:{
						unit: {
							required: true,
							message: "请选择单位",
							trigger: "change"
						},
						warehouse: {
							required: true,
							message: "请选择仓库",
							trigger: "change"
						},
						batch: {
							required: true,
							message: "请输入批次号",
							trigger: "blur"
						},
						mfd: {
							required: true,
							message: "请选择生产日期",
							trigger: "change"
						},
						price: [
							{
								required: true,
								message: "请输入单价",
								trigger: "blur"
							},
							{
								validator: (rule,value,callback)=>{
									this.$lib.synValidate('money',value)?callback():callback(new Error('单价不正确'));
								},
								trigger: "blur"
							}
						],
						nums: [
							{
								required: true,
								message: "请输入数量",
								trigger: "blur"
							},
							{
								validator: (rule,value,callback)=>{
									this.$lib.synValidate('nums',value)?callback():callback(new Error('数量不正确'));
								},
								trigger: "blur"
							}
						],
						discount: [
							{
								required: true,
								message: "请输入折扣率",
								trigger: "blur"
							},
							{
								validator: (rule,value,callback)=>{
									this.$lib.validate('percentage',value)?callback():callback(new Error('折扣率不正确'));
								},
								trigger: "blur"
							}
						],
						tax:[
							{
								required: true,
								message: "请输入税率",
								trigger: "blur"
							},
							{
								validator: (rule,value,callback)=>{
									this.$lib.validate('percentage',value)?callback():callback(new Error('税率不正确'));
								},
								trigger: "blur"
							}
						]
					}
				},
				list:{
					data:[],
					focus:0,
					more:0,
					attr:0
				},
				page: {
					current: 1,
					total: 0,
					size: 30,
					sizes: [30, 60, 90, 150, 300],
					count: 5
				},
				memory:{
					data:[],
					focus:0
				},
				panel:{
					dialog:false,
					active:'cash',
					form:{
						id: 0,
						customer:null,
						time:"",
						number:"",
						total:0,
						actual:"",
						integral:0,
						cost:0,
						account:null,
						ptm:'cash',
						ptn:'',
						people:null,
						logistics:{key:'auto',name:'自动识别',number:""},
						file:[],
						data:"",
						examine:0,
						check:1,
						more:{}
					},
					info:[],
					payment:{
						state:0,
						money:"",
						give:0,
						wechat:{
							code:"",
							tip:""
						},
						ali:{
							code:"",
							tip:""
						},
						other:{}
					}
				},
				report:{
					dialog:false,
					auto:false,
					source:{}
				}
			};
		},
		created() {
			this.init();//初始数据
			this.getDeploy(); //零售配置
			this.record(1); //获取数据
		},
		computed: {
			//读取数据中心
			store(){
				return this.$store.state;
			},
			//商品列表列数
			listColumn:{
				cache: false,
				get(){
					let width = document.body.offsetWidth;
					if (width >= 1920) {
						return 6; //XL
					} else if (width >= 1200) {
						return 4; //LG
					} else if (width >= 992) {
						return 3; //MD
					} else if (width >= 768) {
						return 2; //SM
					} else {
						return 1; //XS
					}
				}
			},
			paymentName(){
				let obj={cash:'现金',wechat:'微信',ali:'支付宝'};
				for (let item of this.deploy.other) {
					obj[item.key]=item.name;
				}
				return obj[this.panel.form.ptm];
			}
		},
		methods: {
			//初始化数据
			init(){
				this.panel.form.customer=this.deploy.base.customer;
				this.panel.form.time=this.$moment().format('YYYY-MM-DD');
				this.panel.form.number='POS'+this.$moment().format('YYMMDDHHmmssS')+this.$lib.randomNumber(3);
				this.panel.form.account=this.deploy.base.account;
			},
			//综合-零售配置
			getDeploy(){
				this.$axios.post("service/getDeploy").then(result => {
					if (result.state == "success") {
						if(result.info==null){
							this.$loading({
								lock: true,
								text: '零售参数未配置',
								spinner: 'el-icon-set-up',
								background: 'rgba(0,0,0,0.7)'
							});
						}else{
							this.deploy=result.info;
							//兼容三方支付
							if(this.deploy.other.length>0){
								for (let item of this.deploy.other) {
									this.$set(this.panel.payment.other,item.key,'')
								}
							}
							//初始化数据
							this.init();
						}
					} else if (result.state == "error") {
						this.$message({type: "warning",message: result.info});
					} else {
						this.$message({type: "error",message: "[ ERROR ] 服务器响应超时!"});
					}
				});
			},
			//列表-获取数据
			record(page) {
				page==0||(this.page.current=page);
				let model=this.search.model=='code'?true:false;
				let obj=model?{code:this.search.value}:{'mate':this.search.value};
				let parm = Object.assign({page: this.page.current,limit:this.page.size},obj);
				this.$axios.post("service/goodsRecord", parm).then(result => {
					if (result.state == "success") {
						if(model){
							this.search.value="";
							if (result.count == 0) {
								this.$message({type: "warning",message: "未匹配到商品数据!"});
								return false;
							} else if (result.count == 1) {
								let goods=result.info[0],source;
								if(goods.attr.length>1){
									this.$message({type: "warning",message: "匹配到多个辅助属性!"});
								}else{
									if(goods.attr.length==0){
										source=Object.assign({},goods,{attr:'',nums:1});
									}else{
										let attr=goods.attr[0];
										source=Object.assign({},goods,{attr:attr.name,buy:attr.buy,sell:attr.sell,retail:attr.retail,code:attr.code});
									}
									this.pushGoods(source);
									return false;
								}
							} else {
								this.$message({type: "warning",message: "匹配到多个商品信息!"});
							}
						}
						//常规场景
						this.list.data = result.info;
						this.list.focus=0;
						this.list.more=0;
						this.page.total = result.count;
						document.querySelector('.listCard').focus();
					} else if (result.state == "error") {
						this.$message({type: "warning",message: result.info});
					} else {
						this.$message({type: "error",message: "[ ERROR ] 服务器响应超时!"});
					}
				});
			},
			//客户改变[客户等级]
			customerChange(parm){
				this.customer.grade="";
				if(parm)this.customer.grade=parm.grade;
			},
			//列表-切换录入模式
			switchModel(){
				this.search.model=this.search.model=='mate'?'code':'mate';
			},
			//列表-录入焦点
			modelFocus(){
				let el=document.querySelector('.goodsInput > input');
				el.focus();
				el.select();
			},
			//列表-商品点击
			goodsClick(goods,index){
				this.list.more=0;
				this.list.focus=index;
				if(goods.attr.length>0){
					this.list.more=goods.id;
					this.list.attr=0;
					this.$nextTick(()=>{document.querySelector('.goodsCardFocus .goodsMore').focus()});
				}else{
					let source=this.$lib.extend(true,{},goods,{attr:''});
					this.pushGoods(source);
				}
			},
			//列表-辅助属性点击
			attrClick(attr,index){
				this.list.attr=index;
				let goods=this.list.data.find(item=>item.id==attr.pid);
				let source=Object.assign({},goods,{attr:attr.name,buy:attr.buy,sell:attr.sell,retail:attr.retail,code:attr.code});
				this.pushGoods(source);
			},
			//数据-添加商品
			pushGoods(goods){
				let findIndex=this.table.data.findIndex(item=>(item.key==goods.id && item.attr==goods.attr))
				if(findIndex==-1){
					let priceType='retail';
					let row = {
						key: goods.id,
						goodsType:goods.type,
						unitData: goods.units,
						unitRelation:{valence:goods[priceType],multiple:1},
						batchType:goods.batch,
						validityType:goods.validity,
						serialType:goods.serial,
						name: goods.name,
						attr: goods.attr,
						unit: goods.unit=='-1'?'':goods.unit,
						warehouse:goods.type==0?this.deploy.base.warehouse:0,
						batch:'',
						mfd:'',
						price:goods[priceType],
						nums: 1,
						serial:[],
						retreat: 0,
						discount: this.$lib.gradeDiscount(this.customer.grade,goods.strategy),
						dsc: 0,
						total: null,
						tax: this.store.sys.fun.rate,
						tat: null,
						tpt: null,
						data: ''
					};
					//价格计算
					let money=this.$calc.chain(row.price).multiply(row.nums).round(this.store.sys.fun.digit.money).done();
					row.dsc = this.$calc.chain(money).divide(100).multiply(row.discount).round(this.store.sys.fun.digit.money).done();
					row.total = this.$calc.chain(money).subtract(row.dsc).done();
					row.tat = this.$calc.chain(row.total).divide(100).multiply(row.tax).round(2).done();
					row.tpt = this.$calc.chain(row.total).add(row.tat).done();
					this.table.data.push(row);
					this.table.focus=this.table.data.length-1;
				}else{
					//自增数量|兼容多单位
					let obj = this.table.data[findIndex]
					obj['nums']=this.$calc.chain(1).divide(obj.unitRelation.multiple).add(obj['nums']).done();
					this.table.focus=findIndex;
				}
				this.handleTable();
				this.tableFocus();
			},
			//数据-表格验证
			handleTable(){
				let effect=true;
				let data = this.table.data;
				//数据处理
				let serials=[];
				for (var i = 0; i < data.length; i++) {
					if (data[i].unitData.length>0 && this.$lib.validate('empty',data[i].unit)) {
						effect==true&&(effect="商品信息第" + (i + 1) + "行单位不可为空!");
						continue;
					} else if (data[i].goodsType==0 && data[i].warehouse==null) {
						effect==true&&(effect="商品信息第" + (i + 1) + "行仓库不可为空!");
						continue;
					} else if (data[i].batchType && this.$lib.validate('empty',data[i].batch)) {
						effect==true&&(effect="商品信息第" + (i + 1) + "行批次号不可为空!");
						continue;
					} else if (data[i].validityType && this.$lib.validate('empty',data[i].mfd)) {
						effect==true&&(effect="商品信息第" + (i + 1) + "行生产日期不可为空!");
						continue;
					} else if (!this.$lib.synValidate('money',data[i].price)) {
						effect==true&&(effect="商品信息第" + (i + 1) + "行单价不正确!");
						continue;
					} else if (!this.$lib.synValidate('nums',data[i].nums)) {
						effect==true&&(effect="商品信息第" + (i + 1) + "行数量不正确!");
						continue;
					} else if (data[i].serialType && data[i].serial.length==0) {
						effect==true&&(effect="商品信息第" + (i + 1) + "行序列号不可为空!");
						continue;
					} else if (data[i].serialType && this.$calc.chain(data[i].serial.length).divide(data[i].unitRelation.multiple).done()!=data[i].nums) {
						effect==true&&(effect="商品信息第" + (i + 1) + "行序列号与数量不符!");
						continue;
					} else if (!this.$lib.validate('percentage',data[i].discount)) {
						effect==true&&(effect="商品信息第" + (i + 1) + "行折扣率不正确!");
						continue;
					} else if (!this.$lib.validate('percentage',data[i].tax)) {
						effect==true&&(effect="商品信息第" + (i + 1) + "行税率不正确!");
						continue;
					} else {
						let money = this.$calc.chain(data[i].price).multiply(data[i].nums).round(this.store.sys.fun.digit.money).done();
						data[i].dsc = this.$calc.chain(money).divide(100).multiply(data[i].discount).round(this.store.sys.fun.digit.money).done();
						data[i].total = this.$calc.chain(money).subtract(data[i].dsc).done();
						data[i].tat = this.$calc.chain(data[i].total).divide(100).multiply(data[i].tax).round(2).done();
						data[i].tpt = this.$calc.chain(data[i].total).add(data[i].tat).done();
						serials=serials.concat(data[i].serial);//转存序列号组合
					}
				}
				//场景判断
				if(effect==true){
					//序列号重复验证
					if(serials.length!=this.$lib.distinct(serials).length){
						message && this.$message({type: "warning",message: "商品信息中存在重复序列号!"});
						return false;
					}
					//转存数据
					this.$set(this.panel,'info',data.map(item=>{
						return {
							goods: item.key,
							attr: item.attr,
							unit: item.unit,
							warehouse: item.warehouse,
							batch: item.batch,
							mfd: item.mfd,
							price: item.price,
							nums: item.nums,
							serial :item.serial,
							discount: item.discount,
							dsc: item.dsc,
							total: item.total,
							tax: item.tax,
							tat: item.tat,
							tpt: item.tpt,
							data: item.data
						}
					}));
					this.summary();
				}
				return effect;
			},
			//数据-删除表格数据
			delTable(index){
				this.table.data.splice(index,1);
				this.table.focus=-1;
				this.handleTable();
			},
			//数据-清空数据表格
			clearTable(){
				this.table.data=[];
				this.table.focus=-1;
				this.table.total=0;
				this.$message({type: "success",message: "已重置商品信息"});
			},
			//数据-商品统计
			summary(){
				let total=this.$calc.chain(0);
				this.table.data.forEach((item)=>{
				   total=total.add(item.tpt);
				});
				this.table.total=total.done();
			},
			//参数-单位变化
			unitChange(value){
				let data=this.table.data[this.table.focus];
				let relation = this.$lib.unitRelation(value,data.unitData,this);
				data.unitRelation.multiple=relation.multiple;
				data.price=this.$calc.chain(data.unitRelation.valence).multiply(relation.multiple).multiply(relation.discount).round(this.$store.state.sys.fun.digit.money).done();
				if(data.unitData.length>0 && data.hasOwnProperty('serial') && data.serial.length>0){
					data.nums=this.$calc.chain(data.serial.length).divide(relation.multiple).done();
				}
				this.handleTable();
			},
			//参数-库存弹层开启
			showStock(){
				let data=this.table.data[this.table.focus];
				this.form.stockCondition={goods:data.key,attr:data.attr};
				this.form.stock=true;
				this.$nextTick(()=>{document.querySelector('body > .v-modal').focus()});
			},
			//参数-库存弹层行事件
			stockRowClick(row){
				this.table.data[this.table.focus].warehouse=row.warehouse;
				this.handleTable();
			},
			//参数-库存弹层关闭
			stockClose(){
				this.form.stock=false;
				document.querySelector('.el-popover .form').focus();
			},
			//参数-批次弹层开启
			showLot(){
				let data=this.table.data[this.table.focus];
				if(data.warehouse==null){
					this.$message({type: "warning",message: "请先选择仓库信息"});
				}else{
					this.form.lotCondition={goods:data.key,attr:data.attr,warehouse:data.warehouse};
					this.form.lot=true;
					this.$nextTick(()=>{document.querySelector('body > .v-modal').focus()});
				}
			},
			//参数-批次弹层行事件
			lotRowClick(row){
				let data=this.table.data[this.table.focus];
				data.batch=row.number;
				if(row.time!=null){
					data.mfd=row.time;
				}
				this.handleTable();
			},
			//参数-批次弹层关闭
			lotClose(){
				this.form.lot=false;
				document.querySelector('.el-popover .form').focus();
			},
			//参数-序列号弹层
			showSnu(){
				let data=this.table.data[this.table.focus];
				if(data.warehouse==null){
					this.$message({
						type: "warning",
						message: "请先选择仓库信息"
					});
				}else if(data.batchType && data.batch==''){
					this.$message({
						type: "warning",
						message: "请先输入批次信息"
					});
				}else if(data.validityType && data.mfd==''){
					this.$message({
						type: "warning",
						message: "请先输入生产日期"
					});
				}else{
					this.form.snuConfig={
						record:{show:true,parm:{state:0}},
						source:data.serial,
						condition:{
							warehouse:data.warehouse,
							goods:data.key,
							attr:data.attr,
							batch:data.batch,
							mfd:data.mfd
						}
					}
					this.form.snu=true;
					this.$nextTick(()=>{document.querySelector('body > .v-modal').focus()});
				}
			},
			//参数-序列号保存
			saveSnu(serial){
				let data=this.table.data[this.table.focus];
				if(data.unitData.length>0){
					data.nums=this.$calc.chain(serial.length).divide(data.unitRelation.multiple).done();
				}else{
					data.nums=serial.length;
				}
				data.serial=serial;
				this.handleTable();
			},
			//参数-序列号弹层关闭
			snuClose(){
				this.form.snu=false;
				document.querySelector('.el-popover .form').focus();
			},
			//参数-商品详情ESC
			formEscEvent(e){
				this.table.popover=false;
				document.querySelector('.tableCard').focus();
			},
			//信息-表格选中样式
			tableFocus(){
				let table=this.table;
				if(table.data.hasOwnProperty(table.focus)){
					this.$nextTick(() => {
						let list = document.querySelectorAll(".goodsTable tbody tr");
						list.forEach(el=>{
							el.classList.remove("tableFocus");
						});
						list[table.focus].classList.add("tableFocus");
					})
				}
			},
			//信息-表格行点击
			tableRowClick(row){
				this.table.focus=this.table.data.findIndex(item=>(item.key==row.key && item.attr==row.attr));
				this.table.popover=true;
				this.tableFocus();
				this.$nextTick(()=>{document.querySelector('.el-popover .form').focus()});
			},
			//综合-组件复位
			vposClick(){
				this.list.more=0;
			},
			//综合-全屏提示
			fullScreenTip() {
				this.$message({type: "success",message: "按下 F11 切换显示模式"});
			},
			//综合-挂单
			pend(){
				if(this.table.data.length>0){
					this.memory.data.push({time:this.$moment().format('HH:mm:ss'),data:this.table.data});
					this.table.data=[];
					this.table.focus=-1;
					this.table.total=0;
					this.$message({type: "success",message: "挂单成功"});
				}else{
					this.$message({type: "warning",message: "商品信息不可为空!"});
				}
			},
			//源单-弹层显示
			pendPopperShow(){
				if(this.memory.data.length>0){
					this.$nextTick(()=>{
						document.querySelector('.pendList').focus();
					})
				}
			},
			//源单-恢复源单
			restore(index){
				this.table.data=this.memory.data[index].data;
				this.memory.data.splice(index,1);
				this.memory.focus=0
				this.handleTable();
				this.$refs["pendPopper"].showPopper=false;
				this.$message({type: "success",message: "恢复源单成功"});
			},
			//源单-删除源单
			delPend(index){
				this.memory.data.splice(index,1);
				this.memory.focus=0;
				if(this.memory.data.length==0){
					this.$refs["pendPopper"].showPopper=false;
				}
			},
			//综合-结账
			settle(){
				//数据验证
				if(this.panel.form.customer==null){
					this.$message({type: "warning",message: "请选择客户"});
				}else{
					if(this.table.data.length>0){
						let effect=this.handleTable();
						if(effect==true){
							//单据数据
							this.panel.form.total=this.table.total;
							this.panel.form.actual=this.table.total;
							this.panel.form.integral=this.$calc.chain(this.table.total).multiply(this.store.sys.vend.ratio).done();
							//显示弹层
							this.panel.dialog=true;
							//扩展处理
							this.$nextTick(()=>{
								this.handPaymentTabs();
							})
						}else{
							this.$message({type: "warning",message: effect});
						}
					}else{
						this.$message({type: "warning",message: "商品信息为空"});
					}
				}
			},
			//综合-Tabs点击
			paymentTabsClick(){
				this.handPaymentTabs();
			},
			//综合-处理Tabs选中
			handPaymentTabs(){
				let form=this.panel.form;
				let active=this.panel.active;
				this.$nextTick(()=>{
					if(active=='cash'){
						document.querySelector('.paymentMoney').focus();
						document.querySelector('.paymentMoney').select();
						form.account=this.deploy.base.account;
					}else if(active=='wechat'){
						this.$refs.paymentWechat.focus();
						this.$refs.paymentWechat.select();
						form.account=this.deploy.wechat.account;
					}else if(active=='ali'){
						this.$refs.paymentAli.focus();
						this.$refs.paymentAli.select();
						form.account=this.deploy.ali.account;
					}else{
						let inputRef='payment'+active;
						this.$refs[inputRef][0].focus();
						this.$refs[inputRef][0].select();
						this.deploy.other.forEach(item=>{
							if(item.key==active){
								form.account=item.account;
							}
						});
					}
					form.ptm=active;
				});
			},
			//实际金额改变
			actualChange(){
				let actual=this.panel.form.actual;
				if(this.$lib.synValidate('money',actual)){
					this.panel.form.integral=this.$calc.chain(actual).multiply(this.store.sys.vend.ratio).done();
					this.paymentMoneyChange();
				}else{
					this.panel.form.integral=0;
				}
			},
			//实收金额改变
			paymentMoneyChange(){
				let money=this.panel.payment.money;
				if(this.$lib.synValidate('money',money)){
					let give=this.$calc.chain(money).subtract(this.panel.form.actual).done();
					this.panel.payment.give=give>0?give:0;
				}else{
					this.panel.payment.give=0;
				}
			},
			//综合-结算键盘
			board(key){
				if(['1','2','3','4','5','6','7','8','9','00','0','.'].indexOf(key)!=-1){
					this.panel.payment.money+=key;
					this.paymentMoneyChange();
					document.querySelector('.paymentMoney').focus();
				}else if(key=='del'){
					this.panel.payment.money=this.panel.payment.money.substring(0,this.panel.payment.money.length-1);
					this.paymentMoneyChange();
					document.querySelector('.paymentMoney').focus();
				}else if(key=='empty'){
					this.panel.payment.money="";
					this.paymentMoneyChange();
					document.querySelector('.paymentMoney').focus();
				}else{
					this.final();
				}
			},
			//综合-结算
			final(){
				let form=this.panel.form;
				let payment=this.panel.payment;
				//结算中阻止调用
				if(payment.state===1)return false;
				if(!this.$lib.synValidate('money',form.actual)){
					this.$message({
						type: "warning",
						message: '实际金额不正确!'
					});
				}else if(form.actual-0>form.total-0){
					this.$message({
						type: "warning",
						message: '实际金额不可大于单据金额!'
					});
				}else if(!this.$lib.synValidate('money',form.integral)){
					this.$message({
						type: "warning",
						message: '单据积分不正确!'
					});
				}else if(form.actual!=0 && form.account==null){
					this.$message({
						type: "warning",
						message: '结算账户不可为空!'
					});
				}else{
					if(this.panel.active=='cash'){
						//现金
						if(!this.$lib.synValidate('money',payment.money)){
							this.$message({type: "warning",message: '收款金额不正确!'});
						}else if(payment.money-0<form.actual-0){
							this.$message({type: "warning",message: '收款金额小于应收金额!'});
						}else{
							this.save();
						}
					}else if(this.panel.active=='wechat'){
						//微信
						if(this.$lib.validate('empty',form.ptn)){
							if(this.$lib.validate('empty',payment.wechat.code)){
								this.$message({type: "warning",message: '请扫描顾客付款码!'});
								return false;
							}else{
								//触发支付
								this.scanPay('wechat','pay');
							}
						}else{
							this.save();
						}
					}else if(this.panel.active=='ali'){
						//支付宝
						if(this.$lib.validate('empty',form.ptn)){
							if(this.$lib.validate('empty',payment.ali.code)){
								this.$message({type: "warning",message: '请扫描顾客付款码!'});
								return false;
							}else{
								//触发支付
								this.scanPay('ali','pay');
							}
						}else{
							this.save();
						}
					}else{
						let key=this.panel.active;
						form.ptm=key;
						form.ptn=payment.other[key];
						this.save();
					}
				}
			},
			//扫码支付
			scanPay(type,fun){
				let form=this.panel.form;
				let payment=this.panel.payment;
				payment.state=1;
				let pay=()=>{
					this.$axios.post(type+"/pay", {
						number:form.number,
						money:form.actual,
						code:payment[type].code
					}).then(result => {
						if (result.state == "success") {
							//支付成功
							this.save();
							payment[type].tip='成功收款 [ '+form.actual+' ]';
							form.ptm=type;
							form.ptn=result.info;
						} else if (result.state == "wait") {
							//查询单据
							query();
						} else if (result.state == "wrong") {
							//支付失败
							payment.state=0;
							this.handPaymentTabs();
							payment[type].tip=result.info;
							form.number='POS'+this.$moment().format('YYMMDDHHmmssS');
						} else if (result.state == "error") {
							payment.state=0;
							this.$message({type: "warning",message: result.info});
						} else {
							payment.state=0;
							this.$message({type: "error",message: "[ ERROR ] 服务器响应超时!"});
						}
					});
				}
				//查询单据
				let queryNums=10;
				let query=()=>{
					payment[type].tip='获取支付结果中...';
					this.$axios.post(type+"/query", {
						number:form.number,
					}).then(result => {
						if (result.state == "success") {
							//支付成功
							this.save();
							payment[type].tip='成功收款 [ '+form.actual+' ]';
							form.ptm=type;
							form.ptn=result.info;
						} else if (result.state == "wait") {
							//等待状态|轮询单据
							queryNums--;
							let load = this.$loading({lock: true,text: "[ "+queryNums+" ]"+" 获取支付结果中...",background: "rgba(0, 0, 0, 0.3)"});
							setTimeout(()=>{
								load.close();
								queryNums==1?cancel():query();
							},2000);
						} else if (result.state == "wrong") {
							//撤销单据
							cancel();
						} else if (result.state == "error") {
							payment.state=0;
							this.$message({type: "warning",message: result.info});
						} else {
							payment.state=0;
							this.$message({type: "error",message: "[ ERROR ] 服务器响应超时!"});
						}
					});
				}
				//撤销单据
				let cancel=()=>{
					payment[type].tip='正在撤销单据...';
					this.$axios.post(type+"/cancel", {
						number:form.number,
					}).then(result => {
						if (result.state == "success") {
							//撤销成功-更新单据号
							this.handPaymentTabs();
							payment.state=0;
							payment[type].code="";
							payment[type].tip='单据已撤销，请重新收款!';
							form.number='POS'+this.$moment().format('YYMMDDHHmmssS');
							form.ptn="";
						} else if (result.state == "wait") {
							//等待状态|轮询单据
							query();
						} else if (result.state == "wrong") {
							payment.state=0;
							this.handPaymentTabs();
							payment[type].tip=result.info;
						} else if (result.state == "error") {
							payment.state=0;
							this.$message({type: "warning",message: result.info});
						} else {
							payment.state=0;
							this.$message({type: "error",message: "[ ERROR ] 服务器响应超时!"});
						}
					});
				}
				eval(fun+'()');
			},
			//提交数据
			save(){
				this.panel.payment.state=1;
				this.$axios.post("vend/save", {
					class:this.panel.form,
					info:this.panel.info,
					cost:[]
				}).then(result => {
					if (result.state == "success") {
						this.panel.form.id=result.info;
						this.$message({
							type: "success",
							message: "审核单据中..."
						});
						setTimeout(()=>{this.examine()},996);
					} else if (result.state == "error") {
						this.panel.payment.state=0;
						this.$alert(result.info, '警告');
					} else {
						this.panel.payment.state=0;
						this.$alert('[ ERROR ] 服务器响应超时!', '警告');
					}
				});
			},
			//审核单据
			examine(){
				this.panel.payment.state=1;
				this.$axios.post("vend/examine", {
					parm:[this.panel.form.id],
				}).then(result => {
					if (result.state == "success") {
						this.print(true);
						this.panel.payment.state=2;
						document.querySelector('.el-dialog__wrapper').focus();
					} else if (result.state == "error") {
						this.$confirm(result.info, "警告", {
							confirmButtonText: "继续",
							cancelButtonText: "取消",
							type: "warning"
						}).then(() => {
							this.print(true);
							this.panel.payment.state=2;
							document.querySelector('.el-dialog__wrapper').focus();
						}).catch(()=>{
							this.panel.payment.state=0;
						});
					} else {
						this.panel.payment.state=0;
						this.$alert('[ ERROR ] 服务器响应超时!', '警告');
					}
				});
			},
			//打印单据
			print(auto){
				this.$report.init().then(()=>{
					this.report.auto=auto;
					this.report.source={
						vend:{parm:[this.panel.form.id]}
					};
					this.report.dialog=true;
					this.$nextTick(()=>{
						document.querySelector('.v-modal').focus();
					})
				});
			},
			//开始打印
			startPrint(type){
				if(type==0){
					setTimeout(()=>{
						document.querySelector('.v-modal').focus();
					},996);
				}
			},
			//打印关闭
			viewerDestroy(){
				this.report.auto=false;
				this.report.source={};
				this.report.dialog=false;
				this.$nextTick(()=>{
					document.querySelector('.el-dialog__wrapper').focus();
				})
			},
			//综合-完成收银
			reduction(){
				this.table.data=[];
				this.table.focus=-1;
				this.table.total=0;
				this.panel.dialog=false;
				this.panel.active="cash";
				this.panel.form.id=0;
				this.panel.form.customer=this.deploy.base.customer;
				this.panel.form.time=this.$moment().format('YYYY-MM-DD');
				this.panel.form.number='POS'+this.$moment().format('YYMMDDHHmmssS')+this.$lib.randomNumber(3);
				this.panel.form.total=0;
				this.panel.form.actual="";
				this.panel.form.integral=0;
				this.panel.form.account=this.deploy.base.account;
				this.panel.form.ptm="cash";
				this.panel.form.ptn="";
				this.panel.form.data="";
				this.panel.info=[];
				this.panel.payment.state=0;
				this.panel.payment.money="";
				this.panel.payment.give=0;
				this.panel.payment.ali.tip="";
				this.panel.payment.ali.code="";
				this.panel.payment.wechat.tip="";
				this.panel.payment.wechat.code="";
				for (let v in this.panel.payment.other) {
					this.panel.payment.other[v]="";
				}
			},
			//综合-结账关闭
			panelClose(){
				this.panel.payment.state==2&&this.reduction();
			},
			//商品文本框事件
			goodsInputEvent(e){
				let keyCode=e.keyCode;
				if([13,37,38,39,40].indexOf(keyCode)!=-1){
					keyCode==13&&this.record(1);
					e.stopPropagation();
				}
			},
			//信息-表格按键事件
			tableCardEvent(e){
				let keyCode=e.keyCode;
				if([13,27,38,40,46].indexOf(keyCode)!=-1){
					let table=this.table;
					if(keyCode==13){
						//回车
						this.table.popover=true;
						this.$nextTick(()=>{document.querySelector('.el-popover .form').focus()});
					} else if(keyCode==27){
						//ESC
						this.table.popover=false;
						document.querySelector('.tableCard').focus();
					} else if(keyCode==38){
						//上
						table.data.hasOwnProperty(table.focus-1)&&table.focus--;
					} else if(keyCode==40){
						//下
						table.data.hasOwnProperty(table.focus+1)&&table.focus++;
					}else if(keyCode==46){
						//DEL
						this.delTable(table.focus);
					}
					//处理滚动条位置 方向键有效
					if([38,40].indexOf(keyCode)!=-1 && table.data.length>0){
						this.$nextTick(()=>{
							let el=document.querySelector('.tableFocus');
							let dom=document.querySelector('.el-table__body-wrapper');
							if(el){
								while(this.$lib.isDomVisual(el,dom,'y')==false){
									if(keyCode==38){
										dom.scrollTop--;
									}else{
										dom.scrollTop++;
									}
								}
							}
						})
					}
					//表格样式
					this.tableFocus();
				}
			},
			//列表-按键事件
			listEvent(e){
				let keyCode=e.keyCode;
				if([13,37,38,39,40].indexOf(keyCode)!=-1){
					let list=this.list;
					if(keyCode==13){
						//回车
						this.goodsClick(list.data[list.focus],list.focus);
					} else if(keyCode==37){
						//左
						list.data.hasOwnProperty(list.focus-1)&&list.focus--;
					} else if(keyCode==38){
						//上
						list.data.hasOwnProperty(list.focus-this.listColumn)&&(list.focus=list.focus-this.listColumn);
					} else if(keyCode==39){
						//右
						list.data.hasOwnProperty(list.focus+1)&&list.focus++;
					} else if(keyCode==40){
						//下
						list.data.hasOwnProperty(list.focus+this.listColumn)&&(list.focus=list.focus+this.listColumn);
					}
					//处理滚动条位置 方向键有效
					if([37,38,39,40].indexOf(keyCode)!=-1 && list.data.length>0){
						this.$nextTick(()=>{
							let el=document.querySelector('.goodsCardFocus');
							let dom=document.querySelector('.goodsList');
							while(this.$lib.isDomVisual(el,dom,'y')==false){
								if([37,38].indexOf(keyCode)!=-1){
									dom.scrollTop--;
								}else{
									dom.scrollTop++;
								}
							}
						})
					}
				}
			},
			//列表-属性按键事件
			goodsMoreEvent(e){
				let keyCode=e.keyCode;
				if([13,27,38,40].indexOf(keyCode)!=-1){
					let list=this.list;
					let goods=list.data[list.focus];
					if(keyCode==13){
						//回车
						this.attrClick(goods.attr[list.attr],list.attr);
					} else if(keyCode==27){
						//ESC
						this.list.more=0;
						document.querySelector('.listCard').focus();
					} else if(keyCode==38){
						//上
						goods.attr.hasOwnProperty(list.attr-1)&&list.attr--;
					} else if(keyCode==40){
						//下
						goods.attr.hasOwnProperty(list.attr+1)&&list.attr++;
					}
					//处理滚动条位置 方向键有效
					if([38,40].indexOf(keyCode)!=-1){
						this.$nextTick(()=>{
							let el=document.querySelector('.attrListFocus');
							let dom=document.querySelector('.attrList');
							while(this.$lib.isDomVisual(el,dom,'y')==false){
								if(keyCode==38){
									dom.scrollTop--;
								}else{
									dom.scrollTop++;
								}
							}
						})
					}
				}
			},
			//源单-按键事件
			pendEvent(e){
				let keyCode=e.keyCode;
				if([13,27,38,40,46].indexOf(keyCode)!=-1){
					let memory=this.memory;
					if(keyCode==13){
						//回车
						this.restore(memory.focus);
					}else if(keyCode==27){
						//ESC
						this.$refs["pendPopper"].showPopper=false;
					}else if(keyCode==38){
						//上
						memory.data.hasOwnProperty(memory.focus-1)&&memory.focus--;
					}else if(keyCode==40){
						//下
						memory.data.hasOwnProperty(memory.focus+1)&&memory.focus++;
					}else if(keyCode==46){
						//DEL
						this.delPend(memory.focus);
						this.$refs["pendPopper"].showPopper=false;
					}
					//处理滚动条位置 方向键有效
					if([38,40].indexOf(keyCode)!=-1){
						this.$nextTick(()=>{
							let el=document.querySelector('.pendFocus');
							let dom=document.querySelector('.pendList');
							while(this.$lib.isDomVisual(el,dom,'y')==false){
								if([37,38].indexOf(keyCode)!=-1){
									dom.scrollTop--;
								}else{
									dom.scrollTop++;
								}
							}
						})
					}
				}
			},
			//结账-按键事件
			panelEvent(e){
				let keyCode=e.keyCode;
				//支付状态
				if(this.panel.payment.state==0){
					//等待支付
					if(e.altKey){
						//组合按键
						if([49,50,51].indexOf(keyCode)!=-1){
							if(keyCode==49){
								this.panel.active='cash';
							}else if(keyCode==50){
								if(this.deploy.wechat.enable){
									this.panel.active='wechat';
								}
							}else{
								if(this.deploy.ali.enable){
									this.panel.active='ali';
								}
							}
							this.handPaymentTabs();
						}else if([52,53,54,55,56,57].indexOf(keyCode)!=-1){
							let index=keyCode-52;
							if(this.deploy.other.hasOwnProperty(index)){
								this.panel.active=this.deploy.other[index].key;
								this.handPaymentTabs();
							}
						}
					}else{
						//常规按键
						if(keyCode==27){
							//ESC
							this.panel.dialog=false;
						}else if(keyCode==120){
							//F9 结算
							this.final();
						}
					}
				}else if(this.panel.payment.state==2){
					//支付完成
					if(keyCode==27){
						//ESC
						this.panel.dialog=false;
					}else if(keyCode==121){
						//F10 结算
						this.print(false);
					}
				}
			}
		},
		mounted() {
			//综合-监听按键
			document.addEventListener("keydown",(e)=>{
				let keyCode=e.keyCode;
				//38,40 阻止滚动
				if([38,40,112,113,114,115,116,117,118,119,120,123].indexOf(keyCode)!=-1){
					if(keyCode==112){
						//F1 获取录入焦点
						this.modelFocus();
					}else if(keyCode==113){
						//F2 切换录入模式
						this.switchModel();
					}else if(keyCode==114){
						//F3 激活商品列表
						document.querySelector('.listCard').focus();
					}else if(keyCode==115){
						//F4 激活商品信息
						document.querySelector('.tableCard').focus();
					}else if(keyCode==116){
						//F5 清空商品
						if(document.activeElement.className.indexOf('tableCard')>-1){
							this.$confirm("您确定要清空商品吗?", "提示", {
								confirmButtonText: "ENTER 确定",
								cancelButtonText: "ESC 取消",
								type: "warning"
							}).then(() => {
								this.clearTable();
							}).catch(()=>{});
						}
					}else if(keyCode==118){
						//F7 挂单模式
						this.pend();
					}else if(keyCode==119){
						//F8 源单模式
						this.$refs["pendPopper"].showPopper=!this.$refs["pendPopper"].showPopper;
					}else if(keyCode==120){
						//F9 结账模式
						this.settle();
					}else if(keyCode==123){
						//F12 客户模式
						this.$refs.customerList.switchState();
					}
					e.preventDefault();
					e.returnValue=false;
				}
			});
		}
	};
</script>
<style>
	.el-loading-mask .el-icon-set-up{
		color: #fff;
		font-size: 32px;
	}
	.vposFormPopover{
		padding: 0;
		border-radius: 4px;
	}
	.el-popconfirm__main{
		line-height: 32px;
	}
</style>
<style scoped>
	/* 综合布局 */
	.container {
		height: 100vh;
	}
	.el-header {
		color: #FFFFFF;
		line-height: 60px;
		background: #0F86EA;
		border-bottom: 1px solid #006de0;
	}
	.content {
		height: 0;
		padding: 12px;
		background: #f4f5f7;
	}
	.el-aside {
		width: 25% !important;
		min-width: 360px;
		overflow: initial;
		margin-right: 6px;
	}
	.el-main {
		padding: 0;
		margin-left: 6px;
		overflow: initial;
	}
	.el-footer {
		padding-top: 12px;
		background: #FFFFFF;
		box-shadow: 0 2px 12px 0 rgba(0, 0, 0, .1);
	}

	/* 综合-操作图标 */
	.headerList {
		float: right;
	}
	.headerList li {
		margin: 0 6px;
		display: inline-block;
	}
	.headerCustomer{
		width: 132px;
	}
	.headerCustomer >>> .el-input__inner{
		color: #DCDFE6;
		font-size: 12px;
		background: #0F86EA;
	}

	/* 综合-主面板 */
	.contentCard {
		height: 100%;
		outline: none;
		box-sizing: border-box;
	}
	.contentCard:focus >>> .el-card__header{
		border-bottom: 1px solid #c1c1c1;
	}
	.contentCard>>>.el-card__header{
		padding: 6px 20px;
		background: #fdfdfd;
	}
	.contentCard>>> > .el-card__body{
		position: relative;
		padding: 10px;
		height: calc(100% - 40px);
		box-sizing: border-box;
	}
	.contentCard >>> .el-card__header span,.contentCard >>> .el-card__header i {
		line-height: 28px;
	}
	/* 卡片底部 */
	.cardFooter{
		position: absolute;
		left: 0;
		right: 0;
		bottom: 0;
		height: 40px;
		background: #fdfdfd;
		border-top: 1px solid #EBEEF5;
	}

	/* 商品信息-列表选中 */
	.goodsCardFocus{
		border: 1px solid #999;
	}

	/* 商品表格 */
	.goodsTable{
		height: 100%;
	}
	/* 商品表格-行选中 */
	.goodsTable >>> .tableFocus{
		background: #f4f5f7;
	}
	.goodsTable >>> .tableFocus:focus{
		background: #f4f5f7;
	}

	/* 商品信息-汇总 */
	.tableCount{
		line-height: 38px;
	}
	.tableCount p{
		margin-left: 12px;
	}
	.tableCount span{
		text-align: right;
		display: block;
		color: #ff6a23;
		margin-right: 12px;
	}

	/* 商品参数-弹层 */
	.formPopover{
		position: absolute;
		top: -26px;
		right: 6px;
	}
	.formPopover .ico{
		display: none;
	}

	/* 商品参数-商品面板 */
	.form{
		outline: none;
	}
	.form *{
		outline: none;
	}
	.form .title{
		padding: 10px;
		background: rgb(253, 253, 253);
		border-bottom: 1px solid #c1c1c1;
	}
	.form .el-form{
		max-height: calc(100vh - 208px);
		overflow-x: hidden;
		overflow-y: auto;
		padding: 10px 12px;
	}
	.form .el-form-item{
		width: 250px;
		margin-bottom: 14px;
	}
	.form .el-date-editor{
		width: auto;
	}
	.form >>> .el-input-group__append{
		padding: 0 12px;
		cursor: pointer;
	}

	/* 列表-头部组件 */
	.clearTable {
		float: right;
	}
	.goodsInput{
		width: 260px;
		float: right;
	}
	.goodsInputModel {
		width: 72px;
	}
	.goodsCard{
		position: relative;
		height: 250px;
	}

	/* 列表-商品区域 */
	.goodsList{
		height: calc(100% - 30px);
		overflow-x: hidden;
		overflow-y: auto;
	}
	.goodsList .listCol{
		margin-bottom: 12px;
	}
	.goodsCard{
		cursor: pointer;
	}
	.goodsCard >>> .el-card__body{
		height: 100%;
		padding: 10px;
		box-sizing: border-box;
	}

	/* 列表-属性标签 */
	.goodsMoreTip{
		position: absolute;
		top: 6px;
		right: -16px;
		width: 66px;
		height: 20px;
		background: #dc8253;
		transform: rotate(45deg);
		box-shadow: 0 2px 12px 0 rgba(0,0,0,.1);
	}
	.goodsMoreTip span{
		display: block;
		color: #FFFFFF;
		font-size: 12px;
		line-height: 20px;
		text-align: center;
	}

	/* 列表-常规界面 */
	.goodsRoutine .goodsImg{
		width:100%;
		height: 200px;
		object-fit:cover;
		border-radius: 2px;
	}
	.goodsRoutine .goodsImgSlot{
		display: flex;
		width: 100%;
		height: 200px;
		border-radius: 2px;
		background: #f5f7fa;
		align-items: center;
		justify-content: center;
	}
	.goodsRoutine .goodsImgSlot span{
		color: #909399;
	}
	.goodsRoutine .info p{
		overflow: hidden;
		line-height: 32px;
		white-space: nowrap;
		text-overflow:ellipsis;
	}
	.goodsRoutine .info span{
		display: block;
		color: #ff6a23;
		text-align: right;
		line-height: 32px;
	}

	/* 列表-属性界面 */
	.goodsMore{
		height: 100%;
		outline: none;
	}
	.goodsMore .title{
		padding: 6px 12px;
		box-sizing: border-box;
		border-bottom: 1px solid #EBEEF5;
	}
	.goodsMore .attrList{
		height: calc(100% - 32px);
		overflow: auto;
		list-style-type:none;
	}
	.goodsMore .attrList li{
		cursor: pointer;
		padding: 3px 6px;
		margin: 9px 12px;
		border: 1px solid #dcdfe6;
	}
	.goodsMore .attrList p{
		font-size: 12px;
	}
	.goodsMore .attrList span{
		display: block;
		font-size: 12px;
		color: #ff6a23;
		text-align: right;
	}

	/* 列表-属性选中 */
	.attrListFocus{
		border: 1px solid #999 !important;
	}

	/* 列表-空商品 */
	.listEmpty{
		color: #ccc;
		margin-top: 12px;
		text-align: center;
	}
	.listEmpty i{
		font-size: 50px;
	}
	.listEmpty p{
		line-height: 2.25rem;
		letter-spacing: 3px;
	}

	/* 分页样式 */
	.el-pagination{
		text-align: center;
		margin-top: 3px;
	}
	.el-pagination >>> .btn-prev,.el-pagination >>> .btn-next{
		padding: 0;
		min-width: 24px;
	}
	.el-pagination >>> .el-pager li{
		min-width: 24px;
	}
	.el-pagination >>> .el-pagination__jump{
		margin-left: 6px;
	}
	.el-pagination >>> .el-pagination__sizes{
		margin: 0 6px 0 6px;
	}

	/* 源单 */
	.emptyPend{
		line-height: 32px;
		text-align: center;
	}
	.pendList{
		outline: none;
	}
	.pendList li{
		display: flex;
		justify-content: space-between;
		align-content: space-between;
	}
	.pendList li span{
		margin-left: 6px;
	}
	.pendList li i{
		line-height: 32px;
		margin-right: 6px;
	}
	.pendList .pendFocus{
		background: #f4f5f7;
	}

	/* 结账面板 */
	.panel{
		outline: none;
	}
	.panel >>> .el-card__header{
		padding: 9px 12px;
	}
	.panel >>> .el-card__body{
		padding: 12px;
	}
	.panel >>> .el-tabs__header{
		margin-left: 0;
	}
	.panel >>> .el-tabs__content{
		padding: 12px;
	}
	.panel .el-form-item{
	    margin-bottom: 12px;
	}
	.panel >>> .el-input-group__prepend , .panel >>> .el-input-group__append{
		padding: 0 10px;
		cursor: pointer;
	}

	/* 现金结算 */
	.panel .cashList{
		padding: 6px 0px;
		border-radius: 4px;
		background: #f5f7fa;
		border: 1px solid #dcdfe6;
		list-style-type: none;
		color: #303133;
	}
	.panel .cashList li{
		float: left;
		width: 33%;
		text-align: center;
	}
	.panel .cashList span{
		line-height: 24px;
		font-weight: bold;
	}
	.panel .cashList p{
		height: 24px;
		line-height: 24px;
		font-size: 20px;
		margin-top: 2px;
	}
	.panel .cashList input{
		width: 100%;
		background: #f5f7fa;
		border: none;
		height: 24px;
		font-size: 20px;
		outline: none;
		text-align: center;
		border-bottom:1px solid #dcdfe6;
	}
	.panel .cashList:after{
		clear:both;
		content:"";
		display:block;
	}

	/* 结算标签 */
	.panel .paymentTabs{
		height: 286px;
		border-radius: 4px;
	}
	.panel .paymentTip{
		position: absolute;
		right: 26px;
		bottom: 9px;
		font-size: 12px;
		color: #c6c9ce;
	}
	.panel .cashGive{
		color: #fe5b00;
		padding: 0 6px;
		font-size: 16px;
		text-align: right;
		font-weight: bold;
		border-radius: 2px;
		border: 1px solid #dcdfe6;
	}

	/* 结算键盘 */
	.panel .board{
		margin-top: 9px;
	}
	.panel .board ul{
		list-style: none;
		box-sizing: border-box;
		border-top: 1px solid #dcdfe6;
		border-left: 1px solid #dcdfe6;
	}
	.panel .board ul:after{
		clear:both;
		content:"";
		display:block;
	}
	.panel .board li{
		float: left;
		width: 25%;
		cursor: pointer;
		font-size: 12px;
		font-weight: bold;
		line-height: 46px;
		text-align: center;
		box-sizing: border-box;
		border-right: 1px solid #dcdfe6;
		border-bottom: 1px solid #dcdfe6;
	}
	.panel .board .stress{
		width: 50%;
		color: #fff;
		margin-top: -1px;
		margin-left: -1px;
		background: #51a7ff;
		box-sizing: initial;
		border-right: 1px solid #51a7ff;
		border-bottom: 2px solid #51a7ff;
	}

	/* 扫码样式 */
	.panel .scan{
		text-align: center;
		margin-top: 24px;
	}
	.panel .scan > i{
		font-size: 32px;
		background: #f5f7fa;
		border: 1px solid #dcdfe6;
		padding: 12px;
		border-radius: 100%;
	}
	.panel .scan > span{
		display: block;
		margin: 12px 0;
	}
	.panel .scan .el-input{
		width: 200px;
	}
	.panel .scan .el-input >>> .el-input__inner{
		background: #fbfbfb;
	}
	.panel .scan > p{
		padding: 0 10px;
		margin-top: 12px;
		line-height: 24px;
		border-radius: 4px;
		background: #f5f7fa;
		border: 1px solid #dcdfe6;
	}

	/* 完成小票 */
	.panel .mouth{
		width: 100%;
		height: 24px;
		border-radius: 3px;
		background: #b3b3b3;
		box-sizing: border-box;
		border: 6px solid #e4e4e4;
	}
	.panel .paper{
		margin: 0 auto;
		font-size: 12px;
		margin-top: -14px;
		padding: 6px 10px;
		border-radius: 3px;
		width: calc(100% - 24px);
		box-sizing: border-box;
		background-color: #FFF;
		border: 1px solid #EBEEF5;
		box-shadow: 0 2px 12px 0 rgba(0,0,0,.1);
	}
	.panel .paper .title{
		text-align: center;
	}
	.panel .paper .distribute{
		display: flex;
		justify-content: space-between;
		align-content: space-between;
	}
	.panel .paper>*{
		line-height: 24px;
	}

	/* 完成样式 */
	.panel .finish{
		margin: 14px 0;
		text-align: center;
	}
	.panel .finish i{
		font-size: 32px;
		color: #bedaaa;
		border: 1px solid #bedaaa;
		padding: 10px;
		border-radius: 100%;
	}
	.panel .finish p{
		font-size: 16px;
		letter-spacing: 6px;
		margin: 12px 0 20px 0;
	}
	.panel .finish .operate .el-button--primary{
		background: #4ba8f9;
		border-color: #4ba8f9;
	}
	.panel .finish .operate .el-button--success{
		background: #5ecca9;
		border-color: #5ecca9;
	}

	/* 遮罩层 */
	.vposModal{
		position: fixed;
		left: 0;
		top: 0;
		right: 0;
		bottom: 0;
		opacity: .2;
		background: #000;
		z-index: 2;
	}
	/* 自适应 */
	@media only screen and (max-width: 992px){
		.panel .paymentTabs{
			margin-top: 12px;
		}
		.panel .finishCard{
			margin-top: 12px;
		}
	}
</style>