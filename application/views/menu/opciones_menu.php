
<!--
** Melbin Cruz
** Primer if, si no tiene padre, es porque el es un padre.
** 
-->


<?php   
	foreach ($menu_principal as $key => $value) {
		?>
			
				<?php 
					if(!isset($value['sic_padre'])){ 
						?>
						<li>
							<a href="#"><i class="<?php echo $value['sic_icono'];?>"></i> <?php echo $value['sic_nombre']; ?><span class="fa arrow"></span></a>
						<?php
						// Llamar a los hijos
						?> <ul class="nav nav-second-level"> <?php

						foreach ($menu_principal as $key2 => $value2) {
							if($value2['sic_padre']==$value['sic_id']){
								?>
									<li><a href="<?php echo base_url() . $info_padre['sio_nombre'] .'/'. strtolower($value['sic_nombre']) .'/'. str_replace(" ", "_", strtolower($value2['sic_nombre'])) ?>" ><?= $value2['sic_nombre']; ?></a></li>
								<?php
							} 
						}
						?> </ul></li> <?php
					} 
				}
 ?>
