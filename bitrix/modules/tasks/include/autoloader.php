<?
global $DBType;

CModule::AddAutoloadClasses(
	'tasks',
	array(
		'CTasks'                 => 'classes/general/task.php',
		'CTaskMembers'           => 'classes/general/taskmembers.php',
		'CTaskTags'              => 'classes/general/tasktags.php',
		'CTaskFiles'             => 'classes/general/taskfiles.php',
		'CTaskDependence'        => 'classes/general/taskdependence.php',
		'CTaskTemplates'         => 'classes/general/tasktemplates.php',
		'CTaskSync'              => 'classes/general/tasksync.php',
		'CTaskReport'            => 'classes/general/taskreport.php',
		'CTasksWebService'       => 'classes/general/taskwebservice.php',
		'CTaskLog'               => 'classes/general/tasklog.php',
		'CTaskNotifications'     => 'classes/general/tasknotifications.php',
		'CTaskElapsedTime'       => 'classes/general/taskelapsed.php',
		'CTaskReminders'         => 'classes/general/taskreminders.php',
		'CTasksReportHelper'     => 'classes/general/tasks_report_helper.php',
		'CTasksNotifySchema'     => 'classes/general/tasks_notify_schema.php',
		'CTasksPullSchema'       => 'classes/general/tasks_notify_schema.php',
		'CTaskComments'          => 'classes/general/taskcomments.php',
		'CTaskFilterCtrl'        => 'classes/general/taskfilterctrl.php',
		'CTaskAssert'            => 'classes/general/taskassert.php',
		'CTaskItemInterface'     => 'classes/general/taskitem.php',
		'CTaskItem'              => 'classes/general/taskitem.php',
		'CTaskPlannerMaintance'  => 'classes/general/taskplannermaintance.php',
		'CTasksRarelyTools'      => 'classes/general/taskrarelytools.php',
		'CTasksTools'            => 'classes/general/tasktools.php',
		'CTaskSubItemAbstract'   => 'classes/general/subtaskitemabstract.php',
		'CTaskCheckListItem'     => 'classes/general/checklistitem.php',
		'CTaskElapsedItem'       => 'classes/general/elapseditem.php',
		'CTaskLogItem'           => 'classes/general/logitem.php',
		'CTaskCommentItem'       => 'classes/general/commentitem.php',
		'CTaskRestService'       => 'classes/general/restservice.php',
		'CTaskListCtrl'          => 'classes/general/tasklistctrl.php',
		'CTaskListState'         => 'classes/general/taskliststate.php',
		'CTaskIntranetTools'     => 'classes/general/intranettools.php',
		'CTaskTimerCore'         => 'classes/general/timercore.php',
		'CTaskTimerManager'      => 'classes/general/timermanager.php',
		'CTaskCountersProcessor' => 'classes/general/countersprocessor.php',
		'CTaskCountersQueue'     => 'classes/general/countersprocessor.php',
		'CTaskCountersProcessorInstaller'   => 'classes/general/countersprocessorinstaller.php',
		'CTaskCountersProcessorHomeostasis' => 'classes/general/countersprocessorhomeostasis.php',
		'CTaskCountersNotifier'             => 'classes/general/countersnotifier.php',
		'CTaskColumnList'                   => 'classes/general/columnmanager.php',
		'CTaskColumnContext'                => 'classes/general/columnmanager.php',
		'CTaskColumnManager'                => 'classes/general/columnmanager.php',
		'CTaskColumnPresetManager'          => 'classes/general/columnmanager.php',

		'Bitrix\Tasks\Internals\DataBase\Helper'    => "lib/internals/database/helper/".ToLower($DBType).".php",
		'\Bitrix\Tasks\Internals\DataBase\Helper'   => "lib/internals/database/helper/".ToLower($DBType).".php",

		'\Bitrix\Tasks\ActionNotAllowedException'				=> "lib/exception.php",
		'\Bitrix\Tasks\ActionFailedException'					=> "lib/exception.php",
		'\Bitrix\Tasks\AccessDeniedException'					=> "lib/exception.php",
		'\Bitrix\Tasks\ActionRestrictedException'				=> "lib/exception.php",

		'\Bitrix\Tasks\Internals\DataBase\Tree\NodeNotFoundException'			=> "lib/internals/database/tree/exception.php",
		'\Bitrix\Tasks\Internals\DataBase\Tree\TargetNodeNotFoundException'		=> "lib/internals/database/tree/exception.php",
		'\Bitrix\Tasks\Internals\DataBase\Tree\ParentNodeNotFoundException'		=> "lib/internals/database/tree/exception.php",
		'\Bitrix\Tasks\Internals\DataBase\Tree\LinkExistsException'				=> "lib/internals/database/tree/exception.php",
		'\Bitrix\Tasks\Internals\DataBase\Tree\LinkNotExistException'			=> "lib/internals/database/tree/exception.php",

		'\Bitrix\Tasks\Dispatcher\EntityNotFoundException'		=> "lib/dispatcher/exception.php",
		'\Bitrix\Tasks\Dispatcher\MethodNotFoundException'		=> "lib/dispatcher/exception.php",
		'\Bitrix\Tasks\Dispatcher\BadQueryException'			=> "lib/dispatcher/exception.php",
	)
);