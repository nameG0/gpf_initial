<a href="<?=gpf::url('..manage')?>">管理</a>
<hr />
<form action="" method="POST" enctype="multipart/form-data">
模型名：
<input type="text" name="data[name]" value="" />
<br />
昵称：
<input type="text" name="data[nickname]" value="" />
<br />
模型表名：
<input type="text" name="data[tablename]" value="" />
<br />
模型类型：[下拉框]
<select name="data[modeltype]" id="modeltype">
	<option value="0">单表</option>
</select>
<br />
模型类型自己的设置表单，在选中模型类型后通过 ajax 加载出来。
<br />
<input type="submit" name="dosubmit" value="提交" />
</form>
