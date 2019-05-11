import VueRoute from "vue-router";
import Vue from "vue";

import About from "./pages/About";
import Help from "./pages/Help";
import bottomNav from "./components/Commons/BottomNav";
import topNav from "./components/Commons/TopNav";
import works from "./pages/Works";
import schedules from "./pages/Schedules";
import messages from "./pages/Messages";
import organizations from "./pages/Organizations";
import mine from "./pages/Mine";
import createTask from "./components/Works/Tasks/CreateTask";
import createNotice from "./components/Works/Notices/CreateNotice";
import createAsk from "./components/Works/Asks/CreateAsk";
import auditTask from "./components/Works/Tasks/AuditTask";
import finishedWorks from "./components/Works/FinishedWorks";
import taskDetailSelf from "./components/Works/Tasks/TaskDetailSelf";
import taskDetail from "./components/Works/Tasks/TaskDetail";
import toAuditTask from "./components/Works/Tasks/ToAuditTask";
import toReportTask from "./components/Works/Tasks/ToReportTask";
import noticeDetail from "./components/Works/Notices/NoticeDetail";
import noticeDetailSelf from "./components/Works/Notices/NoticeDetailSelf";
import askDetail from "./components/Works/Asks/AskDetail";

import invitation from "./components/Organizations/Invitation";
import joinOrg from "./components/Organizations/JoinOrg";
import search from "./components/Organizations/Search";
import deptUserItem from "./components/Organizations/DeptUserList";
import groupList from "./components/Organizations/GroupList";
import frameWork from "./components/Organizations/FrameWork";
import orgPay from "./components/Organizations/OrgPay";
import orgMigrate from "./components/Organizations/OrgMigrate";
import orgSet from "./components/Organizations/OrgSet";
import orgLink from "./components/Organizations/OrgLink";
import orgMerge from "./components/Organizations/OrgMerge";
import batchOperation from "./components/Organizations/BatchOperation";
import applyJoinOrg from "./components/Organizations/ApplyJoinOrg";
import checkJoinOrg from "./components/Organizations/CheckJoinOrg";
import inviteJoinGroup from "./components/Organizations/InviteJoinGroup";
import checkInviteGroup from "./components/Organizations/CheckInviteGroup";
import deptList from "./components/Organizations/DeptList";
import groupUserItem from "./components/Organizations/GroupUserList";
import userInfo from "./components/Organizations/UserInfo";
import inviteJoinDept from "./components/Organizations/InviteJoinDept";
import inviteJoinOrg from "./components/Organizations/InviteJoinOrg";
import qrcodeToLike from "./components/Organizations/QrcodeToLike";
import CheckJoinGroup from  "./components/Organizations/CheckJoinGroup";


import bindTel from "./components/Mine/BindTel";
import editInfo from "./components/Mine/EditInfo";
import setInfo from "./components/Mine/SetInfo";
import registerOrg from "./components/Mine/RegisterOrg";
import userCard from "./components/Mine/UserCard";

import groupSchedules from "./components/schedules/GroupSchedules";
import schedulesCreate from "./components/schedules/SchedulesCreate";
import remindCreate from "./components/schedules/RemindCreate";
import memorialCreate from "./components/schedules/MemorialCreate";
import scheduleDetail from "./components/schedules/ScheduleDetail";

import feedbackList from "./components/Feedback/list";
import feedbackDetail from "./components/Feedback/detail";

// 任务统计
import taskscount from  "./components/Works/Tasks/TasksCount";
// 审批详情
import allrecord from  "./components/Works/AllRecord";


// admin
import textlist from  "./admin/TextList";
import addlist from	"./admin/AddText";
import AdminTop from  "./admin/common/AdminTopNva";
import AdminBottom from "./admin/common/AdminBottimNva";

Vue.use(VueRoute);

const router = new VueRoute({
	mode: "history",
	routes: [{
		path: "/",
		redirect: "/works/0"
	},
	{
		path: "/works/:type",
		name: "works",
		meta: {
			title: "工作"
		},
		components: {
			default: works,
			bNav: bottomNav,
		}
	},
	// schedules
	{
		path: "/schedules",
		name: "schedules",
		meta: {
			title: "日程"
		},
		components: {
			default: schedules,
			bNav: bottomNav
		},

	},
	// admin
	{
		path: "/addlist",
		name: "addlist",
		meta: {
			title: "添加文章"
		},
		components: {
			default: addlist,
			tNav: AdminTop,
			bNav: AdminBottom
		}
	},
	{
		path: "/admin_article",
		name: "textlist",
		meta: {
			title: "文章列表"
		},
		components: {
			default: textlist,
			tNav: AdminTop,
			bNav: AdminBottom
		}
	},


	// message
	{
		path: "/messages",
		name: "messages",
		meta: {
			title: "消息"
		},
		components: {
			default: messages,
			bNav: bottomNav
		}
	},
	{
		path: "/allrecord",
		name: "allrecord",
		meta: {
			title: "审核详情"
		},
		components: {
			default: allrecord,
			tNav: topNav,
		}
	},

	// organizations
	{
		path: "/organizations",
		name: "organizations",
		meta: {
			title: "机构"
		},
		components: {
			default: organizations,
			bNav: bottomNav,
			// children: [
			//  {
			//      path: '/invitation',
			//      name: 'invitation',
			//      component: invitation
			//  }
			// ]
		}
	},
	{
		path: "/organizations/invitation",
		// meta: {
		// 	title: "test"
		// },
		components: {
			tNav: topNav,
			default: invitation
		}
	},
	{
		path: "/organizations/joinOrg",
		meta: {
			title: "创建机构"
		},
		components: {
			tNav: topNav,
			default: joinOrg
		}
	},
	{
		path: "/organizations/search",
		meta: {
			title: "机构中搜索"
		},
		components: {
			tNav: topNav,
			default: search
		}
	},
	{
		path: "/organizations/dept_user_list",
		meta: {
			title: "机构详情"
		},
		components: {
			tNav: topNav,
			default: deptUserItem
		}
	},
	{
		path: "/organizations/group_list",
		meta: {
			title: "我加入的群组列表"
		},
		components: {
			tNav: topNav,
			default: groupList
		}
	},
	{
		path: "/organizations/frameWork",
		meta: {
			title: "机构管理"
		},
		components: {
			tNav: topNav,
			default: frameWork
		}
	},
	{
		path: "/organizations/set",
		components: {
			tNav: topNav,
			default: orgSet
		}
	},
	{
		path: "/organizations/link",
		components: {
			tNav: topNav,
			default: orgLink
		}
	},
	{
		path: "/merge_org_msg/:id",
		name:"/merge_org_msg",
		components: {
			tNav: topNav,
			default: orgMerge
		}
	},
	{
		path: "/organizations/migrate",
		components: {
			tNav: topNav,
			default: orgMigrate
		}
	},
	{
		path: "/organizations/pay",
		components: {
			tNav: topNav,
			default: orgPay
		}
	},
	{
		path: "/batch_operation",
		meta: {
			title: "批量操作成员"
		},
		components: {
			tNav: topNav,
			default: batchOperation
		}
	},
	{
		path: "/apply_join_org",
		meta: {
			title: "加入机构"
		},
		components: {
			tNav: topNav,
			default: applyJoinOrg
		}
	},
	{
		path: "/check_join_org",
		name:"/check_join_org",
		meta: {
			title: "加入机构申请"
		},
		components: {
			tNav: topNav,
			default: checkJoinOrg
		}
	},
	{
		path: "/invite_join_group/:id",
		meta: {
			title: "邀请加入群组"
		},
		components: {
			tNav: topNav,
			default: inviteJoinGroup
		}
	},
	{
		path: "/check_invite_group",
		name:"/check_invite_group",
		meta: {
			title: "加入群组申请"
		},
		components: {
			tNav: topNav,
			default: checkInviteGroup
		}
	},
	{
		path: "/check_join_group",
		name:"/check_join_group",
		meta: {
			title: "是否同意加入群组"
		},
		components: {
			tNav: topNav,
			default: CheckJoinGroup
		}
	},
	{
		path: "/invite_join_org/:code",
		meta: {
			title: "邀请加入机构"
		},
		components: {
			tNav: topNav,
			default: inviteJoinOrg
		}
	},
	{
		path: "/dept_list",
		meta: {
			title: "部门列表"
		},
		components: {
			tNav: topNav,
			default: deptList
		}
	},
	{
		path: "/group_user_list",
		meta: {
			title: "群组成员"
		},
		components: {
			tNav: topNav,
			default: groupUserItem
		}
	},
	{
		path: "/user_info",
		meta: {
			title: "成员详情"
		},
		components: {
			tNav: topNav,
			default: userInfo
		}
	},
	{
		path: "/invite_join_dept",
		components: {
			tNav: topNav,
			default: inviteJoinDept
		}
	},
	{
		path: "/qrcode_to_like",
		components: {
			tNav: topNav,
			default: qrcodeToLike
		}
	},


	// mine
	{
		path: "/mine",
		name: "mine",
		meta: {
			title: "我的"
		},
		components: {
			default: mine,
			bNav: bottomNav
		}
	},
	{
		path: "/mine/bindTel",
		components: {
			tNav: topNav,
			default: bindTel
		}
	},
	{
		path: "/mine/editInfo",
		components: {
			tNav: topNav,
			default: editInfo
		}
	},
	{
		path: "/mine/setInfo",
		components: {
			tNav: topNav,
			default: setInfo
		}
	},
	{
		path: "/mine/registerOrg",
		components: {
			tNav: topNav,
			default: registerOrg
		}
	},
	{
		path: "/mine/userCard",
		components: {
			tNav: topNav,
			default: userCard
		}
	},

	// works
	{
		path: "/create_task",
		meta: {
			title: "创建任务"
		},
		components: {
			default: createTask,
			tNav: topNav
		}
	},
	{
		path: "/create_notice",
		meta: {
			title: "创建通知"
		},
		components: {
			tNav: topNav,
			default: createNotice
		}
	},
	{
		path: "/create_ask",
		meta: {
			title: "创建请示"
		},
		components: {
			tNav: topNav,
			default: createAsk
		}
	},
	{
		path: "/audit_task",
		meta: {
			title: "流转审批"
		},
		components: {
			tNav: topNav,
			default: auditTask
		}
	},
	{
		path: "/finished_works/:type",
		meta: {
			title: "已完成工作"
		},
		components: {
			tNav: topNav,
			default: finishedWorks
		}
	},
	{
		path: "/taskscount",
		name:"/taskscount",
		meta: {
			title: "任务统计"
		},
		components: {
			tNav: topNav,
			default: taskscount
		}
	},
	{
		path: "/task_detail_self/:id",
		name:"/task_detail_self",
		meta: {
			title: "任务详情"
		},
		components: {
			tNav: topNav,
			default: taskDetailSelf
		}
	},
	{
		path: "/task_detail/:id",

		meta: {
			title: "签收任务"
		},

		components: {
			tNav: topNav,
			default: taskDetail
		}
	},
	{
		path: "/to_audit_task/:id",
		meta: {
			title: "审批任务"
		},
		components: {
			tNav: topNav,
			default: toAuditTask
		}
	},
	{
		path: "/to_report_task/:id",
		meta: {
			title: "已上报任务"
		},
		components: {
			tNav: topNav,
			default: toReportTask
		}
	},
	{
		path: "/notice_detail/:id",
		meta: {
			title: "通知详情"
		},
		components: {
			tNav: topNav,
			default: noticeDetail
		}
	},
	{
		path: "/notice_detail_self/:id",
		meta: {
			title: "通知详情"
		},
		components: {
			tNav: topNav,
			default: noticeDetailSelf
		}
	},
	{
		path: "/ask_detail/:id",
		meta: {
			title: "请示详情"
		},
		components: {
			tNav: topNav,
			default: askDetail
		}
	},

	// schedules   :   日程
	{
		path: "/schedules/group",
		meta: {
			title: "群组日程"
		},
		components: {
			tNav: topNav,
			default: groupSchedules
		}
	},
	{
		path: "/schedules/create",
		meta: {
			title: "创建日程"
		},
		components: {
			tNav: topNav,
			default: schedulesCreate
		}
	},
	{
		path: "/schedules/remind/create",
		meta: {
			title: "创建提醒"
		},
		components: {
			tNav: topNav,
			default: remindCreate
		}
	},
	{
		path: "/schedules/memorial/create",
		components: {
			tNav: topNav,
			default: memorialCreate
		}
	},
	{
		path: "/schedule/detail",
		meta: {
			title: "日程详情"
		},
		components: {
			tNav: topNav,
			default: scheduleDetail
		}
	},
	{
		path: "/feedback/list",
		meta: {
			title: "反馈列表"
		},
		components: {
			tNav: topNav,
			default: feedbackList
		}
	},
	{
		path: "/feedback/detail/:id",
		meta: {
			title: "反馈详情"
		},
		components: {
			tNav: topNav,
			default: feedbackDetail
		}
	},
	{
		path: "/about",
		meta: {
			title: "关于都督"
		},
		components: {
			default: About
		}
	},
	{
		path: "/help",
		meta: {
			title: "都督帮助文档"
		},
		components: {
			default: Help
		}
	}
	]
});

router.beforeEach((to, from, next) => {

	if (to.meta.title) {
		document.title = to.meta.title;
	} else {
		document.title = "都督";
	}
	next();
});
export default router;
