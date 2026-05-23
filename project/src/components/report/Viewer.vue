<template>
	<div class="viewer">
		<el-dialog :visible.sync="dialog" title="打印" width="390px" @close="dialogClose" :append-to-body="true" v-madeDialog>
			<ul class="tip">
				<li>该功能仅限基于谷歌内核浏览器中运行。</li>
				<li>通过打印模板的设计功能即可编辑模板。</li>
				<li>使用问题请联系<a :href="store.base.contact" target="_blank">在线客服</a>寻求解决方案。</li>
			</ul>
			<el-divider></el-divider>
				<div style="text-align: center;">
					<span>打印模板：</span>
					<el-select v-model="thisMoulds" >
						<template v-for="item in moulds">
							<el-option :label="item.name" :value="item.id"></el-option>
						</template>
					</el-select>
				</div>
			<el-divider></el-divider>
			<el-row style="text-align:center;">
				<el-col :span="12">
					<el-button type="info" @click="print(0)" >直接打印</el-button>
				</el-col>
				<el-col :span="12">
					<el-button type="info" @click="print(1)" >预览打印</el-button>
				</el-col>
			</el-row>
			<div id="content"></div>
		</el-dialog>
	</div>
</template>
<script>
	export default {
		name: "Viewer",
		props: {
			mould:{
				required: true,
			},
			source:{
				required: true,
			},
			auto:{
				default: false,
			}
		},
		data(){
			return {
				dialog:false,
				moulds:[],
				thisMoulds:null
			}
		},
		computed: {
			//读取数据中心
			store() {
				return this.$store.state;
			}
		},
		methods: {
			//获取模板
			getMoulds(){
				this.$axios.post('mould/select',{key:this.mould}).then(result => {
					if (result.state == "success") {
						if(result.info.length==0){
							this.$message({
								type: "warning",
								message: "模块[ "+this.mould+" ]未查询到模板数据!"
							});
							this.$emit('destroy',true);
						}else{
							this.moulds=result.info;
							this.thisMoulds=result.info[0].id;
							this.dialog=true;
							this.auto&&this.print(0);
						}
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
			//打印操作
			print(type){
				let mould =this.moulds.find(item=>item.id==this.thisMoulds);
				//配置AXIOS并发请求
				let source=[];
				mould.source.forEach(item=>{
					source.push(this.$axios({
						url:item.path,
						method:'POST',
						data:this.source.hasOwnProperty(item.name)?this.source[item.name]:{}
					}));
				})
				//多并发请求数据源
				this.$axios.all(source).then(this.$axios.spread((...items)=>{
					for (let item of items) {
						if(item.hasOwnProperty('state') && item.state=='error'){
							this.$message({
								type: "warning",
								message: item.info
							});
							this.$emit('destroy',true);
							return false;
						}
					}
					//实例化基础类
					let report = new Stimulsoft.Report.StiReport();
					//加载模板代码
					report.load(mould.code);
					//添加数据源
					items.forEach((item,index)=>{
						let dataSet = new Stimulsoft.System.Data.DataSet('source_'+index);
						dataSet.readJson(item);
						report.regData(dataSet.dataSetName, "", dataSet);
					});
					//同步数据源
					report.dictionary.synchronize();
					//判断操作类型
					if(type==0){
						//直接打印
						report.renderAsync(()=>{
							report.print();
							this.$emit('startPrint',0);
						});
					}else if(type==1){
						//实例化预览器配置项
						var options = new Stimulsoft.Viewer.StiViewerOptions();
						options.appearance.fullScreenMode=true;
						options.toolbar.showOpenButton = false;
						options.toolbar.showAboutButton = false;
						options.toolbar.showFullScreenButton=false;
						options.toolbar.showBookmarksButton=false;
						options.toolbar.showParametersButton=false;
						options.toolbar.showResourcesButton=false;
						//实例化预览器
						var viewer = new Stimulsoft.Viewer.StiViewer(options, "StiViewer", false);
						viewer.report = report;
						viewer.renderHtml("content");
						//增加打印监听
						viewer.onPrintReport=()=>{
							this.$emit('startPrint',1);
						}
						//增加退出按钮
						let customButton = viewer.jsObject.SmallButton("exitButton","退出", null,false);
						customButton.action=()=>{
							viewer.jsObject.controls.reportPanel.clear();
							this.$emit('destroy',true);
						}
						let node = viewer.jsObject.controls.toolbar.querySelectorAll("tr[class='stiJsViewerClearAllStyles']");
						let customTd=node[node.length-1].insertCell(-1);
						customTd.className = "stiJsViewerClearAllStyles";
						customTd.appendChild(customButton);
					}else{
						this.$message({
							type: "warning",
							message: "未识别的操作类型!"
						});
					}
				}));
			},
			// 弹层关闭事件
			dialogClose(){
				this.$emit('destroy',true);
			}
		},
		mounted() {
			this.getMoulds();
		}
	};
</script>
<style>
	#StiViewer{
		padding: 9px;
		background: rgba(0, 0, 0, 0.6) !important;
	}
	.stiJsViewerMainPanel{
		background: #FFFFFF;
	}
	#StiViewer_JsViewerMainPanel > .stiJsViewerToolBar:nth-child(2) > div{
		padding: 0!important;
	}
</style>
<style scoped>
	.tip{
		line-height: 26px;
		list-style-type: decimal;
		margin: 0px 12px 12px 12px;
	}
</style>
