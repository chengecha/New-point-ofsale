<br>
<div class="container-fluid">
	<ul class="nav nav-tabs" id="myTabs" data-toggle="tab">
		<li class="active"><a href="#email_tabs" data-toggle="tab" title="<?php echo $this->lang->line('config_email_configuration'); ?>"><?php echo $this->lang->line('config_email'); ?></a></li>
		<li><a href="#message_tabs" data-toggle="tab" title="<?php echo $this->lang->line('config_message_configuration'); ?>"><?php echo $this->lang->line('config_message'); ?></a></li>
		<li><a href="#integrations_tabs" data-toggle="tab" title="<?php echo $this->lang->line('config_integrations_configuration'); ?>"><?php echo $this->lang->line('config_integrations'); ?></a></li>
	</ul>  
		<div class="tab-content">
		<div class="tab-pane active" id="email_tabs"><?php $this->load->view("configs/email_config"); ?></div>
		<div class="tab-pane" id="message_tabs"><?php $this->load->view("configs/message_config"); ?></div>
		<div class="tab-pane" id="integrations_tabs"><?php $this->load->view("configs/integrations_config"); ?></div>
	</div>
</div>