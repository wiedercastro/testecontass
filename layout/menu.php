

<!-- Left side column. contains the logo and sidebar -->
<aside class="main-sidebar">
	<!-- sidebar: style can be found in sidebar.less -->
	<section class="sidebar">

		<!-- sidebar menu: : style can be found in sidebar.less -->
		<ul class="sidebar-menu" data-widget="tree">
			<li class="header">Menu</li>

			
				<li class="<?php if (($dados[0] == 'tarefas') || ($dados[0] == 'tarefas_cadastro') || ($dados[0] == 'tarefas_edita') || ($dados[0] == 'clientes_detalhes')) echo 'active'; ?>">
					<a href="<?php echo PL_PATH_ADMIN; ?>/tarefas">
						<i class="fa fa-users"></i> <span>Tarefas</span>
					</a>
				</li>
			


			
				<li class="<?php if ($dados[0] == 'usuarios') echo 'active'; ?>">
					<a href="<?php echo PL_PATH_ADMIN; ?>/usuarios">
						<i class="fa fa-user-o"></i> <span>Usuários</span>
					</a>
				</li>
			

		</ul>
	</section>
	<!-- /.sidebar -->
</aside>