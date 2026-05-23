<template>
	<div class="deploy area">
		<div class="layout">
			<el-popover ref="searchPopover" popper-class="searchPopover" placement="bottom-start">
				<el-form class="searchFrom" ref="searchFrom"  inline>
					<el-form-item>
						<nodTree v-model="searchFrom.frame" :treeData="store.frame" placeholder="请选择关联组织"></nodTree>
					</el-form-item>
					<el-form-item>
						<el-input placeholder="请输入备注信息" v-model="searchFrom.data" clearable></el-input>
					</el-form-item>
					<el-divider></el-divider>
					<el-button class="searchBtn" icon="el-icon-search" @click="record(1)" ></el-button>
				</el-form>
				<el-button slot="reference" icon="el-icon-more" ></el-button>
			</el-popover>
			<el-button-group>
				<el-button @click="set(0)">新增</el-button>
				<el-button @click="reload">刷新</el-button>
			</el-button-group>
		</div>
		<el-divider></el-divider>
		<el-table :data="tableData" height="calc(100% - 90px)" border>
			<el-table-column prop="frameData.name" label="关联组织" align="center" width="160px"></el-table-column>
			<el-table-column prop="source.base.title" label="零售标题" align="center" width="160px"></el-table-column>
			<el-table-column prop="data" label="备注信息" align="center" width="200px"></el-table-column>
			<el-table-column prop="set" label="相关操作" align="center"  width="190px">
				<template slot-scope="scope">
					<el-button-group>
						<el-button @click="set(scope.row.id)" size="mini">详情</el-button>
						<el-button @click="del(scope.row.id)" size="mini">删除</el-button>
					</el-button-group>
				</template>
			</el-table-column>
		</el-table>
		<el-pagination class="tablePagination" :current-page.sync="page.current" :total="page.total" :page-size.sync="page.size"
		 :page-sizes="page.sizes" :pager-count="page.count" @size-change="record(1)" @current-change="record(0)" layout="prev,pager,next,jumper,sizes,total">
		</el-pagination>
		<el-dialog class="tabsDialog" :visible.sync="dialog" title="零售配置" width="520px" v-madeDialog>
			<transition name="el-fade-in">
				<template v-if="dialog">
					<el-form :model="form" :rules="rules" ref="form" label-width="80px">
						<el-tabs v-model="active">
							<el-tab-pane label="基础资料" name="main">
								<el-form-item label="关联组织" prop="frame">
									<nodTree v-model="form.frame" :treeData="store.frame" placeholder="请选择关联组织"></nodTree>
								</el-form-item>
								<el-form-item label="备注信息">
									<el-input placeholder="请输入备注信息" v-model="form.data"></el-input>
								</el-form-item>
							</el-tab-pane>
							<el-tab-pane label="基础配置" name="base">
								<el-form-item label="零售标题" prop="source.base.title">
									<el-input placeholder="请输入零售标题" v-model="form.source.base.title"></el-input>
								</el-form-item>
								<el-form-item label="关联账户" prop="source.base.account">
									<nodList v-model="form.source.base.account" placeholder="请选择关联账户" action="service/accountRecord" scene="account"></nodList>
								</el-form-item>
								<el-form-item label="默认客户">
									<nodList v-model="form.source.base.customer" placeholder="请选择默认客户" action="service/customerRecord" scene="customer"></nodList>
								</el-form-item>
								<el-form-item label="默认仓库">
									<nodList v-model="form.source.base.warehouse" placeholder="请选择默认仓库" action="service/warehouseRecord" scene="warehouse"></nodList>
								</el-form-item>
							</el-tab-pane>
							<el-tab-pane label="微信配置" name="wechat">
								<el-form-item label="是否启用">
									<el-switch v-model="form.source.wechat.enable"></el-switch>
								</el-form-item>
								<template v-if="form.source.wechat.enable">
									<el-form-item label="支付标题" prop="source.wechat.title">
										<el-input placeholder="请输入支付标题" v-model="form.source.wechat.title"></el-input>
									</el-form-item>
									<el-form-item label="主体号" prop="source.wechat.appid">
										<el-input placeholder="请输入主体号" v-model="form.source.wechat.appid"></el-input>
									</el-form-item>
									<el-form-item label="商户号" prop="source.wechat.mchid">
										<el-input placeholder="请输入商户号" v-model="form.source.wechat.mchid"></el-input>
									</el-form-item>
									<el-form-item label="商户秘钥" prop="source.wechat.mchkey">
										<el-input placeholder="请输入商户秘钥" v-model="form.source.wechat.mchkey"></el-input>
									</el-form-item>
									<el-form-item label="证书内容" prop="source.wechat.certText">
										<el-input type="textarea" v-model="form.source.wechat.certText" placeholder="请输入证书内容" :rows="3"></el-input>
									</el-form-item>
									<el-form-item label="证书秘钥" prop="source.wechat.keyText">
										<el-input type="textarea" v-model="form.source.wechat.keyText" placeholder="请输入证书秘钥" :rows="3"></el-input>
									</el-form-item>
									<el-form-item label="关联账户" prop="source.wechat.account">
										<nodList v-model="form.source.wechat.account" placeholder="请选择关联账户" action="service/accountRecord" scene="account"></nodList>
									</el-form-item>
								</template>
							</el-tab-pane>
							<el-tab-pane label="支付宝配置" name="ali">
								<el-form-item label="是否启用">
									<el-switch v-model="form.source.ali.enable"></el-switch>
								</el-form-item>
								<template v-if="form.source.ali.enable">
									<el-form-item label="支付标题" prop="source.ali.title">
										<el-input placeholder="请输入支付标题" v-model="form.source.ali.title"></el-input>
									</el-form-item>
									<el-form-item label="应用号" prop="source.ali.appid">
										<el-input placeholder="请输入主体号" v-model="form.source.ali.appid"></el-input>
									</el-form-item>
									<el-form-item label="应用私钥" prop="source.ali.private">
										<el-input type="textarea" placeholder="请输入商户号" v-model="form.source.ali.private" :rows="3"></el-input>
									</el-form-item>
									<el-form-item label="接口公钥" prop="source.ali.public">
										<el-input type="textarea" placeholder="请输入商户秘钥" v-model="form.source.ali.public" :rows="3"></el-input>
									</el-form-item>
									<el-form-item label="关联账户" prop="source.ali.account">
										<nodList v-model="form.source.ali.account" placeholder="请选择关联账户" action="service/accountRecord" scene="account"></nodList>
									</el-form-item>
								</template>
							</el-tab-pane>
							<el-tab-pane label="三方支付" name="other">
								<el-table :data="form.source.other" size="mini" class="gridTable" border>
									<el-table-column label="支付名称" align="center" min-width="120px">
										<template slot-scope="scope">
											<input type="text" v-model="scope.row.name" placeholder="支付名称"></input>
										</template>
									</el-table-column>
									<el-table-column label="支付标识" align="center" width="120px">
										<template slot-scope="scope">
											<input type="text" v-model="scope.row.key" placeholder="支付标识"></input>
										</template>
									</el-table-column>
									<el-table-column label="关联账户" align="center" width="120px">
										<template slot-scope="scope">
											<select v-model="scope.row.account" placeholder="关联账户">
												<option :value="0">点击选择</option>
												<template v-for="account in accountData">
													<option :value="account.id">{{account.name}}</option>
												</template>
											</select>
										</template>
									</el-table-column>
									<el-table-column align="center" width="90px">
										<template slot="header" slot-scope="scope">
											<span>相关操作</span> <i class="el-icon-circle-plus-outline" @click="addOther"></i>
										</template>
										<template slot-scope="scope">
											<i class="el-icon-delete" @click="delOther(scope.$index)"></i>
										</template>
									</el-table-column>
								</el-table>
							</el-tab-pane>
						</el-tabs>
					</el-form>
				</template>
			</transition>
			<span slot="footer" class="dialog-footer">
				<el-button @click="dialog = false">取消</el-button>
				<el-button @click="save" type="primary">保存</el-button>
			</span>
		</el-dialog>
	</div>
</template>
<script>
	import NodTree from "@/components/lib/NodTree";
	import NodList from "@/components/lib/NodList";
	export default {
		name: "Deploy",
		components: {
			NodTree,
			NodList
		},
		data() {
			return {
				accountData:[],
				searchFrom: {
					frame:null,
					data: ""
				},
				tableData: [],
				page: {
					current: 1,
					total: 0,
					size: 30,
					sizes: [30, 60, 90, 150, 300],
					count: 5
				},
				dialog: false,
				active:"main",
				form: {
					id: 0,
					frame:null,
					source:{
						base:{
							title:"",
							account:null,
							customer:null,
							warehouse:null
						},
						wechat:{
							enable:false,
							title:"",
							appid:"",
							mchid:"",
							mchkey:"",
							certText:"",
							keyText:"",
							account:null
						},
						ali:{
							enable:false,
							title:"",
							appid:"",
							private:"",
							public:"",
							account:null
						},
						other:[]
					},
					data: ""
				},
				rules: {
					frame: {
						required: true,
						message: "请选择关联组织",
						trigger: "change"
					},
					"source.base.title":{
						required: true,
						message: "请输入零售标题",
						trigger: "blur"
					},
					"source.base.account":{
						required: true,
						message: "请选择关联账户",
						trigger: "change"
					},
					"source.wechat.title":{
						required: true,
						message: "请输入支付标题",
						trigger: "blur"
					},
					"source.wechat.appid":{
						required: true,
						message: "请输入主体号",
						trigger: "blur"
					},
					"source.wechat.mchid":{
						required: true,
						message: "请输入商户号",
						trigger: "blur"
					},
					"source.wechat.mchkey":{
						required: true,
						message: "请输入商户密钥",
						trigger: "blur"
					},
					"source.wechat.certText":{
						required: true,
						message: "请输入证书内容",
						trigger: "blur"
					},
					"source.wechat.keyText":{
						required: true,
						message: "请输入证书秘钥",
						trigger: "blur"
					},
					"source.wechat.account":{
						required: true,
						message: "请选择关联账户",
						trigger: "change"
					},
					"source.ali.title":{
						required: true,
						message: "请输入支付标题",
						trigger: "blur"
					},
					"source.ali.appid":{
						required: true,
						message: "请输入应用号",
						trigger: "blur"
					},
					"source.ali.private":{
						required: true,
						message: "请输入应用私钥",
						trigger: "blur"
					},
					"source.ali.public":{
						required: true,
						message: "请输入接口公钥",
						trigger: "blur"
					},
					"source.ali.account":{
						required: true,
						message: "请选择关联账户",
						trigger: "change"
					}
				}
			};
		},
		created() {
			this.init();
			this.record(1); //获取数据
		},
		computed: {
			//读取数据中心
			store() {
				return this.$store.state;
			}
		},
		methods: {
			//获取数据
			init() {
				this.$axios.post("service/accountRecord",{query:'',page:1,limit:999}).then(result => {
					if (result.state == "success") {
						this.accountData=result.info;
					} else if (result.state == "error") {
						this.$message({
							type: "warning",
							message: result.info
						});
					} else {
						this.$message({
							type: "error",
							message: "[ ERROR ] 服务器响应超时!"
						});
					}
				});
			},
			//获取数据
			record(page) {
				page==0||(this.page.current=page);
				let parm = Object.assign({
					page: this.page.current,
					limit: this.page.size
				}, this.searchFrom);
				this.$axios.post("deploy/record", parm).then(result => {
					if (result.state == "success") {
						this.tableData = result.info;
						this.page.total = result.count;
						this.$refs["searchPopover"].showPopper = false;
					} else if (result.state == "error") {
						this.$message({
							type: "warning",
							message: result.info
						});
					} else {
						this.$message({
							type: "error",
							message: "[ ERROR ] 服务器响应超时!"
						});
					}
				});
			},
			//设置数据
			set(id) {
				//1.初始化数据
				this.form = Object.assign({}, this.$options.data().form);
				//2.请求数据
				if (id > 0) {
					this.$axios.post("deploy/get", {
						id: id
					}).then(result => {
						if (result.state == "success") {
							this.form = result.info;
							this.dialog = true; //显示弹层
						} else if (result.state == "error") {
							this.$message({
								type: "warning",
								message: result.info
							});
						} else {
							this.$message({
								type: "error",
								message: "[ ERROR ] 服务器响应超时!"
							});
						}
					});
				} else {
					this.dialog = true; //显示弹层
				}
			},
			//保存数据
			save() {
				this.$refs["form"].validate(valid => {
					if (valid) {
						//验证三方支付
						let other=this.form.source.other;
						if(other.length>0){
							let keys=[];
							for (var i = 0; i < other.length; i++) {
								if(this.$lib.validate('empty',other[i].name)){
									this.$message({
										type: "warning",
										message: "三方支付第"+(i+1)+"行支付名称不可为空!"
									});
									return false;
								}else if(!this.$lib.validate('variable',other[i].key)){
									this.$message({
										type: "warning",
										message: "三方支付第"+(i+1)+"行支付标识不正确!"
									});
									return false;
								}else if(this.$lib.validate('capital',other[i].key)){
									this.$message({
										type: "warning",
										message: "三方支付第"+(i+1)+"行支付标识必须为全小写!"
									});
									return false;
								}else if(other[i].account==0){
									this.$message({
										type: "warning",
										message: "三方支付第"+(i+1)+"行关联账户不正确!"
									});
									return false;
								}else{
									keys.push(other[i].key);
								}
							}
							if(keys.length!=this.$lib.distinct(keys).length){
								this.$message({
									type: "warning",
									message: "三方支付存在重复支付标识!"
								});
								return false;
							}else if(this.$lib.intersect(keys,['cash','wechat','ali']).size>0){
								this.$message({
									type: "warning",
									message: "支付标识不可使用[ cash | wechat | ali ]"
								});
								return false;
							}
						}
						this.$axios.post("deploy/save", this.form).then(result => {
							if (result.state == "success") {
								this.record(0);
								this.dialog = false;
								this.$lib.updateStore(this);
								this.$message({
									type: "success",
									message: "详情保存成功!"
								});
							} else if (result.state == "error") {
								this.$message({
									type: "warning",
									message: result.info
								});
							} else {
								this.$message({
									type: "error",
									message: "[ ERROR ] 服务器响应超时!"
								});
							}
						});
					}else{
						this.$message({
							type: "warning",
							message: '表单验证错误,请检查并修正!'
						});
					}
				});
			},
			//删除数据
			del(id) {
				this.$confirm("您确定要删除该数据吗?", "提示", {
					confirmButtonText: "确定",
					cancelButtonText: "取消",
					type: "warning"
				}).then(() => {
					this.$axios.post("deploy/del", {
						id: id
					}).then(result => {
						if (result.state == "success") {
							this.record(0);
							this.$message({
								type: "success",
								message: "删除成功!"
							});
						} else if (result.state == "error") {
							this.$message({
								type: "warning",
								message: result.info
							});
						} else {
							this.$message({
								type: "error",
								message: "[ ERROR ] 服务器响应超时!"
							});
						}
					});
				}).catch(()=>{});
			},
			//新增三方支付
			addOther(){
				this.form.source.other.push({name:"",key:"",account:0})
			},
			//删除三方支付
			delOther(index){
				this.form.source.other.splice(index,1);
			},
			//页面刷新
			reload() {
				this.$bus.emit('homeReload',this.$options.name);
				this.$message({
					type: "success",
					message: "页面刷新成功!"
				});
			}
		}
	};
</script>

<style scoped>
	.layout {
		display: flex;
		justify-content: space-between;
	}
</style>
