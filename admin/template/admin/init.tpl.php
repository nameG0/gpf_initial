<form action="<?=gpf::url("..init")?>" method="POST" enctype="multipart/form-data">
	首次登录，初始化登录密码：
	<div >
	输入密码：<input type="password" name="password" value="" />
	</div>
	<div >
	再次输入密码：<input type="password" name="password_again" value="" />
	</div>
	<input type="submit" name="dosubmit" value="确定" />
</form>
