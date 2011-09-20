<ul class="menu">
	<li>
		<a href='/' <?php echo $this->menu == "administration" ? "class='on'" : "" ?> >ADMINISTRATION</a>
		<ul class="menu <?php echo $this->menu == "administration" ? "on" : "" ?>">
			<li><a href='/users/' <?php echo get_class($this) == "users" ? "class='selected'" : "" ?>>Users</a></li>
		</ul>
	</li>
</ul>