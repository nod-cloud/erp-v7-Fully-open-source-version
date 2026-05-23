<template>
	<div class="mould area">
		<div class="layout">
			<el-popover ref="searchPopover" popper-class="searchPopover" placement="bottom-start">
				<el-form class="searchFrom" ref="searchFrom"  inline>
					<el-form-item>
						<el-input placeholder="请输入模板名称" v-model="searchFrom.name" clearable></el-input>
					</el-form-item>
					<el-form-item>
						<el-input placeholder="请输入模板标识" v-model="searchFrom.key" clearable></el-input>
					</el-form-item>
					<el-divider></el-divider>
					<el-button class="searchBtn" icon="el-icon-search" @click="record(1)" ></el-button>
				</el-form>
				<el-button slot="reference" icon="el-icon-more" ></el-button>
			</el-popover>
			<el-button-group>
				<el-button @click="set(0)" >新增</el-button>
				<el-button @click="batch.dialog=true">批量</el-button>
				<el-button @click="reload" >刷新</el-button>
			</el-button-group>
		</div>
		<el-divider></el-divider>
		<el-table :data="tableData" height="calc(100% - 90px)" @selection-change="selectionChange" border v-madeTable>
			<el-table-column type="selection" align="center" width="39px" fixed="left"></el-table-column>
			<el-table-column prop="name" label="模板名称" align="center" width="160px"></el-table-column>
			<el-table-column prop="key" label="模板标识" align="center" width="160px"></el-table-column>
			<el-table-column prop="sort" label="模板排序" align="center" width="90px"></el-table-column>
			<el-table-column prop="data" label="备注信息 " align="center" width="200px"></el-table-column>
			<el-table-column prop="set" label="相关操作" align="center" width="250px">
				<template slot-scope="scope">
					<el-button-group>
						<el-button @click="view(scope.row.id)" size="mini">调试</el-button>
						<el-button @click="set(scope.row.id)" size="mini">详情</el-button>
						<el-button @click="copy(scope.row.id)" size="mini">复制</el-button>
						<el-button @click="del(scope.row.id)" size="mini">删除</el-button>
					</el-button-group>
				</template>
			</el-table-column>
		</el-table>
		<el-pagination class="tablePagination" :current-page.sync="page.current" :total="page.total" :page-size.sync="page.size"
		 :page-sizes="page.sizes" :pager-count="page.count" @size-change="record(1)" @current-change="record(0)" layout="prev,pager,next,jumper,sizes,total">
		</el-pagination>
		<el-dialog :visible.sync="dialog" title="详情" width="620px" v-madeDialog>
			<transition name="el-fade-in">
				<template v-if="dialog">
					<el-form :model="form" :rules="rules" ref="form" label-width="80px" >
						<el-form-item label="模板名称" prop="name">
							<el-input placeholder="请输入模板名称" v-model="form.name"></el-input>
						</el-form-item>
						<el-form-item label="模板标识" prop="key">
							<el-input placeholder="请输入模板标识" v-model="form.key"></el-input>
						</el-form-item>
						<el-form-item label="模板排序" prop="sort">
							<el-input placeholder="请输入模板排序" v-model="form.sort"></el-input>
						</el-form-item>
						<el-table :data="form.source" class="gridTable" size="mini" border style="margin-bottom: 22px;" >
							<el-table-column label="数据源名称" align="center" width="120px">
								<template slot-scope="scope">
									<input type="text" v-model="scope.row.name" placeholder="数据源名称"></input>
								</template>
							</el-table-column>
							<el-table-column label="数据源地址" align="center" min-width="160px">
								<template slot-scope="scope">
									<input type="text" v-model="scope.row.path" placeholder="数据源地址"></input>
								</template>
							</el-table-column>
							<el-table-column label="调试参数" align="center" width="160px">
								<template slot-scope="scope">
									<input type="text" v-model="scope.row.parm" placeholder="调试参数"></input>
								</template>
							</el-table-column>
							<el-table-column label="相关操作"  align="center" width="90px">
								<template slot="header" slot-scope="scope">
									<span>相关操作</span> <i class="el-icon-circle-plus-outline" @click="addSource"></i>
								</template>
								<template slot-scope="scope">
									<i class="el-icon-delete" @click="delSource(scope.$index)"></i>
								</template>
							</el-table-column>
						</el-table>
						<el-form-item label="模板代码" prop="code">
							<el-input type="textarea" :rows="5" placeholder="请输入模板代码" v-model="form.code"></el-input>
						</el-form-item>
						<el-form-item label="备注信息">
							<el-input placeholder="请输入备注信息" v-model="form.data"></el-input>
						</el-form-item>
					</el-form>
				</template>
			</transition>
			<span slot="footer" class="dialog-footer">
				<el-button @click="dialog = false" >取消</el-button>
				<el-button @click="save" type="primary" >保存</el-button>
			</span>
		</el-dialog>
		<el-dialog class="tabsDialog" :visible.sync="batch.dialog" title="批量" width="420px" v-madeDialog>
			<transition name="el-fade-in">
				<template v-if="batch.dialog">
					<el-tabs v-model="batch.active">
						<el-tab-pane label="导入数据" name="import">
							<ul class="importTip">
								<li>1.该功能适用于导入模板数据。</li>
								<li>2.点击获取模板按钮可获取模板文件。</li>
								<li>3.点击上传模板按钮上传模板文件即可。</li>
								<li>4.模板文件开发格式请参阅程序开发文档。</li>
								<li>5.如需帮助可点此<a :href="store.base.contact" target="_blank">联系客服</a>获取相关帮助。</li>
							</ul>
							<el-divider></el-divider>
							<el-row style="text-align:center;">
								<el-col :span="12">
									<el-button @click="getTemplate" type="info" >获取模板</el-button>
								</el-col>
								<el-col :span="12">
									<el-upload
										:action="$base.web+'mould/import'"
										:headers="{Token:$store.state.token}"
										:show-file-list="false"
										:on-success="importCall"
									>
										<el-button type="primary" >上传模板</el-button>
									</el-upload>
								</el-col>
							</el-row>
						</el-tab-pane>
						<el-tab-pane label="导出数据" name="export">
							<div class="exportItem" @click="exports">
								<i class="el-icon-download"></i>	
								<p>导出数据</p>
							</div>
						</el-tab-pane>
					</el-tabs>
				</template>
			</transition>
		</el-dialog>
		<Designer v-if="designer.show" :source="designer.source" @destroy="designerDestroy"></Designer>
	</div>
</template>
<script>
	import Designer from "@/components/report/Designer";
	export default {
		name: "Mould",
		components: {
			Designer
		},
		data() {
			return {
				searchFrom: {
					name: "",
					key:""
				},
				tableData:[],
				tableSelection:[],
				page: {
					current: 1,
					total: 0,
					size: 30,
					sizes: [30, 60, 90, 150, 300],
					count: 5
				},
				dialog: false,
				form: {
					id: 0,
					name:"",
					key:"",
					sort:0,
					source:[],
					code:"",
					data: ""
				},
				rules: {
					name: {
						required: true,
						message: "请输入模板名称",
						trigger: "blur"
					},
					key: {
						required: true,
						message: "请输入模板标识",
						trigger: "blur"
					},
					sort: [
						{
							required: true,
							message:'请输入模板排序',
							trigger: "blur"
						},
						{
							validator: (rule,value,callback)=>{
								this.$lib.validate('number',value)?callback():callback(new Error('模板排序不正确'));
							},
							trigger: "blur"
						}
					],
					code: {
						required:true,
						message:'请输入模板代码',
						trigger:'blur',
					}
				},
				batch:{
					dialog:false,
					active:"import"
				},
				designer:{
					source:0,
					show:false
				}
			};
		},
		created() {
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
			record(page) {
				page==0||(this.page.current=page);
				let parm = Object.assign({
					page: this.page.current,
					limit: this.page.size
				}, this.searchFrom);
				this.$axios.post("mould/record", parm).then(result => { 
					if (result.state == "success") {
						this.tableData = result.info;
						this.page.total = result.count;
						this.$refs["searchPopover"].showPopper=false;
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
				this.form=this.$lib.extend(true,{},this.$options.data().form);
				//2.请求数据
				if (id > 0) {
					this.$axios.post("mould/get", {
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
						if(this.form.source.length==0){
							this.$message({
								type: "warning",
								message: "数据源不可为空!"
							});
						}else{
							for (let i = 0; i < this.form.source.length; i++) {
								if(this.$lib.validate('empty',this.form.source[i].name)){
									this.$message({
										type: "warning",
										message: "数据源第"+(i+1)+"行数据源名称不可为空!"
									});
									return false;
								}else if(this.$lib.validate('empty',this.form.source[i].path)){
									this.$message({
										type: "warning",
										message: "数据源第"+(i+1)+"行数据源地址不可为空!"
									});
									return false;
								}else if(!this.$lib.isJSON(this.form.source[i].parm)){
									this.$message({
										type: "warning",
										message: "数据源第"+(i+1)+"行调试参数不正确!"
									});
									return false;
								}
							}
							this.$axios.post("mould/save", this.form).then(result => {
								if (result.state == "success") {
									this.record(0);
									this.dialog = false;
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
						}
					}
				});
			},
			//复制模板
			copy(id){
				this.$axios.post("mould/copy", {
					id: id
				}).then(result => {
					if (result.state == "success") {
						this.record(0);
						this.$message({
							type: "success",
							message: "复制模板成功!"
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
			},
			//删除数据
			del(id) {
				this.$confirm("您确定要删除该数据吗?", "提示", {
					confirmButtonText: "确定",
					cancelButtonText: "取消",
					type: "warning"
				}).then(() => {
					this.$axios.post("mould/del", {
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
			//添加数据源
			addSource(){
				this.form.source.push({name:'',path:'',parm:'{}'});
			},
			//删除数据源
			delSource(index){
				this.form.source.splice(index,1);
			},
			//模板视图|编辑
			view(id){
				this.$report.init().then(()=>{
					this.designer.source=id;
					this.designer.show=true;
				});
			},
			//模板视图|组件销毁
			designerDestroy(){
				this.designer.source=0;
				this.designer.show=false;
			},
			//获取模板
			getTemplate(){
				window.open(this.store.base.webSite);
			},
			//上传模板回调
			importCall(result, file, fileList){
				if (result.state == 'success') {
					this.$bus.emit('homeReload',this.$options.name);
					this.$message({
						type: "success",
						message: "导入模板成功!"
					});
				} else if (result.state == "error") {
					this.$message({
						type: "warning",
						message: "[ " + file.name + " ]" + result.info
					});
				} else {
					this.$message({
						type: "error",
						message: "[ ERROR ] 服务器响应超时!"
					});
				}
			},
			//导出数据
			exports(){
				if(this.tableSelection.length==0){
					this.$message({
						type: "warning",
						message: "未选择导出数据内容!"
					});
				}else{
					this.$message({
						type: "success",
						message: "[ 导出数据 ] 请求中..."
					});
					let parm=this.$lib.objToParm({parm:this.tableSelection},true);
					setTimeout(() => {
						window.open(this.$base.web+'mould/exports?'+parm)
					}, 1000);
				}
			},
			//表格选中数据改变
			selectionChange(parm) {
				let data = [];
				for (let parmVo of parm) {
					data.push(parmVo.id);
				}
				this.tableSelection = data;
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
