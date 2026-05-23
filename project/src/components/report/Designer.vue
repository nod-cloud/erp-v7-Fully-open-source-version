<template>
	<div class="designer">
		<div id="content"></div>
	</div>
</template>
<script>
	export default {
		name: "Designer",
		props: {
			source:{
				required: true
			}
		},
		methods: {
			//初始化
			init(){
				//获取模板
				this.$axios.post('mould/find',{id:this.source}).then(result => {
					if (result.state == "success") {
						//转存模板配置
						let mould=result.info;
						//配置AXIOS并发请求
						let source=[];
						mould.source.forEach(item=>{
							source.push(this.$axios({url:item.path,method:'POST',data:JSON.parse(item.parm)}));
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
							//实例化编辑器配置项
							let options = new Stimulsoft.Designer.StiDesignerOptions();
							options.appearance.allowChangeWindowTitle = false;
							options.appearance.fullScreenMode=true;
							options.toolbar.showAboutButton = false;
							//实例化编辑器|载入配置项
							let designer = new Stimulsoft.Designer.StiDesigner(options, "StiDesigner", false);
							designer.report = report;//编辑器载入数据
							designer.renderHtml("content");//渲染DOM
							//保存模板操作
							designer.onSaveReport = (event)=>{
								let code = JSON.parse(event.report.saveToJsonString());
								delete code.Dictionary;//移除模板数据源
								//保存模板代码
								this.$axios.post("mould/update",{
									id:this.source,
									code:JSON.stringify(code)
								}).then(result => {
									if (result.state == "success") {
										this.$message({
											type: "success",
											message: "模板文件保存成功!"
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
							//增加退出按钮
							let customButton = designer.jsObject.StatusPanelButton("exitButton","退出", null, null, null, 30, 30);
							customButton.style.marginRight = "3px"
							customButton.action=()=>{
								designer.jsObject.CloseReport();
								this.$emit('destroy',true);
							}
							let customTd = designer.jsObject.options.toolBar.querySelector("tr").insertCell(-1);
							customTd.className = "stiDesignerToolButtonCell";
							customTd.appendChild(customButton);
						}));
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
		},
		mounted() {
			this.init();
		},
	};
</script>
<style>
	#StiDesigner{
		padding: 9px;
		z-index: 999 !important;
		background: rgba(0, 0, 0, 0.6);
	}
	#StiDesignerlocalizationButton{
		display: none !important;
	}
</style>
