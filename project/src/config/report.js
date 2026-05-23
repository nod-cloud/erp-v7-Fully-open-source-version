import Vue from "vue";
import {$LAB} from "#/static/plug/load/lab";
import {Loading} from "element-ui";
let load;
const report={
	init:()=>{
		//检测组件加载状态
		let promise = new Promise((resolve, reject)=>{
			if(window.Stimulsoft){
				resolve(true);
			}else{
				load = Loading.service({lock: true,text: "报表组件加载中...",background: "rgba(0, 0, 0, 0.3)"});
				$LAB.script("/static/plug/zip/zip.js").wait(()=>{
					zip.installJS("/static/plug/report/src/report.zip", ["stimulsoft.reports.js","stimulsoft.viewer.js","stimulsoft.designer.js"],()=>{
						report.loadCss(["/static/plug/report/css/stimulsoft.viewer.office2013.whiteteal.css","/static/plug/report/css/stimulsoft.designer.office2013.whiteteal.css","/static/plug/report/css/stimulsoft.run.css"]);
						Stimulsoft.Base.StiLicense.key ="6vJhGtLLLz2GNviWmUTrhSqnOItdDwjBylQzQcAOiHkcgIvwL0jnpsDqRpWg5FI5kt2G7A0tYIcUygBh1sPs7plofUOqPB1a4HBIXJB621mau2oiAIj+ysU7gKUXfjn/D5BocmduNB+ZMiDGPxFrAp3PoD0nYNkkWh8r7gBZ1v/JZSXGE3bQDrCQCNSy6mgby+iFAMV8/PuZ1z77U+Xz3fkpbm6MYQXYp3cQooLGLUti7k1TFWrnawT0iEEDJ2iRcU9wLqn2g9UiWesEZtKwI/UmEI2T7nv5NbgV+CHguu6QU4WWzFpIgW+3LUnKCT/vCDY+ymzgycw9A9+HFSzARiPzgOaAuQYrFDpzhXV+ZeX31AxWlnzjDWqpfluygSNPtGul5gyNt2CEoJD1Yom0VN9fvRonYsMsimkFFx2AwyVpPcs+JfVBtpPbTcZscnzUdmiIvxv8Gcin6sNSibM6in/uUKFt3bVgW/XeMYa7MLGF53kvBSwi78poUDigA2n12SmghLR0AHxyEDIgZGOTbNI33GWu7ZsPBeUdGu55R8w=";
						Stimulsoft.Base.Localization.StiLocalization.addLocalizationFile("/static/plug/report/lang/zh-CHS.xml",false,"zh-CHS");
						Stimulsoft.Base.Localization.StiLocalization.cultureName="zh-CHS";
						Stimulsoft.Report.Dictionary.StiFunctions.addFunction("custom","","search","Find field contents based on index in specified data source","","","Return to specified content",[Object, String, String, String],["this", "source", "index", "field"],["Reference this object", "Data source name","Specify index fields","Field information"],
							(that, source, index, field)=>{
								let tab = that.dataSources.list.find(obj => obj.name == source);
								if(tab==undefined){
									return "";
								}else{
									let dataTable=tab.dataTable
									let columnIndex = dataTable.columns.list.findIndex(obj => obj.columnName == field);
									let relationIndex = dataTable.columns.list.findIndex(obj => obj.columnName == "relationId");
									let record = dataTable.rows.list.find(obj => obj.itemArray[relationIndex] == index);
									return record?record.itemArray[columnIndex]:"";
								}
								
							}
						);
						load.close();
						resolve(true);
					});
				});
			}
		});
		return promise;
	},
	loadCss(path){
		for (let item of path){
			let link = document.createElement('link');
			link.type = 'text/css';
			link.rel = 'stylesheet';
			link.href = item;
			document.getElementsByTagName('head')[0].appendChild(link);
		}
	}
};
Vue.prototype.$report=report;