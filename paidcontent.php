<?php

/**
 * Plugin Name: ITAP - Parādīt saturu pēc apmaksas
 * Plugin URI: http://itap.lv
 * Description: Spraudnis pēc apmaksas parāda saturu. || Plugin shows content after paid
 * Version: 1.1.7
 * Author: Kristaps Muižnieks for ITAP
 * 
 */
	// aktivizētājs 
	if ( ! defined( 'ABSPATH' ) ) exit;
	 
	register_activation_hook( __FILE__, 'smspcont_aktivizeju' );
	register_activation_hook( __FILE__, 'smspcont_ieliekudatus' );
	 
	function smspcont_aktivizeju () {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$daritmysql = array(
	 
	"CREATE TABLE IF NOT EXISTS `".$wpdb->prefix ."smscenas` (
	  `id` int(255) NOT NULL AUTO_INCREMENT,
	  `cena` int(255) NOT NULL,
	  `nosaukums` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `id` (`id`)
	) $charset_collate;",
	"CREATE TABLE IF NOT EXISTS `".$wpdb->prefix ."smssettings` (
			  `par` varchar(255) NOT NULL,
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `val` varchar(255) NOT NULL,
			  PRIMARY KEY (`id`)
	) $charset_collate;",
	"CREATE TABLE IF NOT EXISTS  `".$wpdb->prefix ."useriarkodiem` (
	  `id` int(255) NOT NULL AUTO_INCREMENT,
	  `kods` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
	  `status` int(4) NOT NULL,
	  `cena` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
	  `ip` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
	  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  PRIMARY KEY (`id`),
	  UNIQUE KEY `id` (`id`)
	)  $charset_collate;",
	"CREATE TABLE IF NOT EXISTS `".$wpdb->prefix ."itapforms` (
	  `id` int(255) NOT NULL AUTO_INCREMENT,
	  `desk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
	  `price` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
	  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
	  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
	  PRIMARY KEY (`id`)
	)  $charset_collate;"
	
	
	);
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			foreach ($daritmysql as $mysqldarbs){
				dbDelta( $mysqldarbs );
				//veidoju tabulas ja nav... 
			}
 
	 
	}
	function smspcont_ieliekudatus () {
	global $wpdb;

	$wpdb->insert( 
		$wpdb->prefix . 'smssettings', 
			array( 
				'id' => '1', 
				'par' => 'apikey', 
				'val' => 'apiatslega', 
			) 
		);
		 $wpdb->insert( 
		 $wpdb->prefix . 'smssettings', 
			array( 
				'id' => '2', 
				'par' => 'kliind', 
				'val' => '999', 
			) 
		);
	}
	
	
	add_action( 'admin_menu', 'smspcont_adminnavigacija' );
	add_shortcode( 'itapcontent', 'smspcont_itapformcontentshortcode' );
	wp_register_style( 'smspcont_stils', plugins_url( 'style.css' , __FILE__ )  );
	add_action( 'admin_notices', 'smscont_adminnotice' );
	 
	function smspcont_adminnavigacija() {
		add_menu_page( 'TB', 'ITAP MAKSAS SATURS ', 'admin', 'galva','dashicons-smartphone','dashicons-smartphone',24);
		add_submenu_page( 'galva', 'APIKEY - ID ', 'APIKEY - ID ', 'manage_options', 'smspcont_iestatijumi','smspcont_iestatijumi');
		add_submenu_page( 'galva', 'PIETEIKTIE KODI', 'PIETEIKTIE KODI', 'manage_options', 'smspcont_pieteiktiekodi','smspcont_pieteiktiekodi');
		add_submenu_page( 'galva', 'SATURA FORMAS', 'SATURA FORMAS', 'manage_options', 'smspcont_contents','smspcont_contents');
		 
	}
	function smscont_adminnotice(){
		wp_enqueue_style('smspcont_stils');
		 global $wpdb;	
		 $apiecho = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$wpdb->prefix."smssettings` where par='apikey' ")); 
		 $clidsecho = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".$wpdb->prefix."smssettings` where par='kliind' ")); 
		
		if ((($apiecho -> val) == 'apiatslega') || (($clidsecho -> val) == 999) || (($clidsecho -> val) == '') || ($apiecho -> val) == ''){
			  echo "<div class='notice notice-success is-dismissible' style='border-color: #3ba1da;'><p>Lūdzu norādiet pareizus APIKEY un klienta ID || Please check your APIKEY and client ID</p></div>";
		 }else{}
 
	}
	function smspcont_iestatijumi(){
		 
		wp_enqueue_style('smspcont_stils');
		 global $wpdb; // JO nepieciešams mysql
		 
										
										
		if (isset($_POST['updatesettings']) && ($_POST['updatesettings'] !='' ) || wp_verify_nonce( $_POST['slepenanonce'], 'apiupdate' ) ){
								if(isset( $_POST ) && !empty( $_POST )){
									
									
												if (!$_POST['api']){
													echo "<div class='denied'> Api nevar būt tukšums! </div>";
												}else if (!$_POST['klientaid']){
													echo "<div class='denied'> Klienta id nevar būt tukšums! </div>";
												}else if (!is_numeric($_POST['klientaid'])){
													echo "<div class='denied'> Klienta id nevar būt teksts! </div>";
												}else{
											
														 

														  $wpdb->query($wpdb->prepare("UPDATE `".$wpdb->prefix."smssettings` SET val='".sanitize_text_field($_POST['api'])."' WHERE  par='apikey' "));
															$wpdb->query($wpdb->prepare("UPDATE `".$wpdb->prefix."smssettings` SET val='".sanitize_text_field($_POST['klientaid'])."' WHERE par='kliind' "));
															echo "Dati samainīti";
														}
											 
											}
								} 								
			 
	 
		  
		 
		 $apiecho = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$wpdb->prefix."smssettings` where par='apikey' ")); 
		 $clidsecho = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".$wpdb->prefix."smssettings` where par='kliind' ")); 
		 
		
		?>

		 <table>
			<tr> 
			 
				<td style="width:30%;float:left">
					<div class="postbox" id="boxid">
								<div title="Click to toggle" class="handlediv"><br></div>
								<h3 class="hndle"><span><center>APIKEY un ID</span></h3>
								<div class="inside" style="text-align:right">
								<p class="description" >APIKEY un ID atrodams Jūsu ITAP profilā -  <i> SMS Uzstādījumi</i> </p>
										<form method="post">
										
										<label><b> APIKEY </b></label><input  type="text" name="api" value="<?php echo $apiecho -> val ?>"><br>
									 
										<label><b>ID</b></label><input type="text" name="klientaid" value="<?php echo  $clidsecho -> val?>"><br>
										 <?php wp_nonce_field( 'apiupdate', 'slepenanonce' ); ?>
										<input type="submit" name="updatesettings"  class="button button-primary"  value="Saglabāt"><br>
											  
										</form>	
								</div>
					</div>
				</td>
				<td style="width:30%;">
					<div class="postbox" id="boxid">
								<div title="Click to toggle" class="handlediv"><br></div>
								<h3 class="hndle"><span><center>Saņemts</span></h3>
								<div class="inside" >
								 
									  <?php
									   $suma = $wpdb->get_row( $wpdb->prepare( "SELECT SUM(cena) as cen FROM ".$wpdb->prefix."useriarkodiem where status = 1 "));
									   $summa = 0;
									    
									    echo "<p style=' text-align: center;font-size: 40px; color: #FF9800;'>".number_format( ($suma->cen) * 0.01, 2, '.', ' ')." €</p>";
									  ?>
										
									 
								<p class="description" >Saņemtā summa var nesakrist ar ITAP kontā saņemto. </p>	 
								</div>
					</div>
				</td>
				<td style="width:40%; ">
				<div class="postbox" id="boxid">
								 
								 
								<div class="inside"><center> 
								 
								<b>ITAP <?php echo date("Y"); ?>. gadā</b></center>
								</div>
								</div>
								
				</td>
				
			</tr>
	 
		 </table>
		<?
		 
	}
 
	
	function smspcont_itapformcontentshortcode( $atts ){
		 
		wp_enqueue_style('smspcont_stils');
	  	$itapformid = $atts['id'];
		if ((!is_numeric($itapformid)) || (!$itapformid) ){
			echo '<div class="denied" style="margin-bottom: 15px;">Shortcode id nav pareizs!</div>';	 
		}else{
					global $wpdb; // JO nepieciešams mysql
					
					 $forma = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$wpdb->prefix."itapforms` where id='$itapformid' ")); 
					 $apiecho = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$wpdb->prefix."smssettings` where par='apikey' ")); 
					 $clidsecho = $wpdb->get_row($wpdb->prepare("SELECT * FROM `".$wpdb->prefix."smssettings` where par='kliind' ")); 
				 
					//sāku pārbaudi datiem
					echo "<div class='descforma'> ". $forma -> desk ."</div>";
						
							
						$client_id =  $apiecho -> val;
						$client_api_key = $clidsecho -> val;
						 
							if( isset( $_POST['key'] ) && !empty( $_POST['key'] ) )
							{
									$answer = file_get_contents("http://itap.lv/sms/unlock/?key=" . $_POST['key'] . "&client=" . $client_id . "&price=" . (int)$forma->price . "&apikey=" . $client_api_key. "&site=".$_SERVER['HTTP_HOST'], FALSE, NULL, 0, 10 );
										if( !$answer )
									{
										echo '<div class="denied" style="margin-bottom: 15px;"><i>file_get_contents()</i> netika izpildīts veiksmīgi. Domājams ka arī nesaņēmi kodu! Ja tev ir kods ienāc pēc 10 minūtem un mēģini vēlreiz, ja nekas nesanāk dod ziņu uz support@itap.lv un mēs paskatīsimes, kas noticis un atrisināsim šo ķibeli</div>';
										exit;
									}
					
				   
										$answer = strtoupper( $answer );
									
										// 1 veiksmīgi
										// 0 neveiksmigi
										if($answer === 'OK') 
										{
												$_SESSION['ITAPANSWER'] = 'ok';
												 
										}
										else if($answer === 'FAILED') 
										{
											$msg = '<div class="denied" style="margin-bottom: 15px;">Kods netika pieņemts. Tas ir nederīgs, jeb jau iztērēts.</div>';
											 
												$_SESSION['ITAPANSWER'] = 'den';
										}
										else if($answer === 'PENDING')
										{
											$msg = '<div class="pending" style="margin-bottom: 15px;">SMS vēl tiek apstrādāta, lūdzu pamēģini vēlreiz pēc pāris minūtēm.</div>';
											 
											$_SESSION['ITAPANSWER'] = 'den';
										} 
										else if($answer === 'ABORTED')
										{
											$msg = '<div class="denied" style="margin-bottom: 15px;">Lūdzu izmaini savā PHP konfigurācijā: <b>allow_url_fopen = On</b> </div>';
										 
											$_SESSION['ITAPANSWER'] = 'den';
										} 
										else
										{
											$msg = '<div class="denied" style="margin-bottom: 15px;">Serveris atbildēja ar neparedzētu paziņojumu: ' . $answer.'</div>';
										 
											$_SESSION['ITAPANSWER'] = 'den';
										}
								}else{
									echo "<div class='denied'> Cena vai atslēga netika norādīta</div> ";
									$_SESSION['ITAPANSWER'] = 'den';
								}
								if ($answer == 'OK'){
									$status = 1 ;	
									}else{
									$status = 0;
								}
							echo $msg;
							if(!empty( $_POST['key'] ) )
							{
							  $wpdb->query( $wpdb->prepare( "INSERT INTO `".$wpdb->prefix."useriarkodiem`(`kods`, `status`,`cena`,`ip`) VALUES ('".sanitize_text_field($_POST['key'])."','".$status."','".$forma->price."','".$_SERVER['HTTP_X_FORWARDED_FOR']."')"));
							}
							
							if ($_SESSION['ITAPANSWER'] == 'ok'){
								
								$formassastavs = $wpdb->get_row( $wpdb->prepare("SELECT * FROM `".$wpdb->prefix."itapforms` where id='$itapformid' ")); 
								switch ($formassastavs -> type){
									case 1:// shortcode 
											echo do_shortcode($formassastavs -> value);
									break;
									case 2:
										echo "<img src='".$formassastavs -> value."'></img>";
									break;
									case 3:
										echo "<div class='accesable'> Tūlīt tiksi pārvirzīts </div>";
										echo '<meta http-equiv="refresh" content="2; url='.$formassastavs -> value.'">';
									break;
									case 4:
									 
										echo "<div class='accesable'> ".$formassastavs ->value . " </div>";
									break;
									default:
									echo 'FAIL';
									break;
								}
								
							}else{
					?>
						<form method="post">
							<table>
								<tr> 
									<td><?php echo "ITAP".$forma->price." uz 144"; ?>  </td>  <td>Kods: <input type="text" name="key" >   </td> 
								</tr>
								<tr> 
									<td colspan=3><input type="submit" name="unclockform" value="Pārbaudīt"></td> 
								</tr>
							</table>
					 
						</form>
					
					<?php
							}
		}// is numeric ... 
		
	}
	function smspcont_pieteiktiekodi(){
		wp_enqueue_style('smspcont_stils');
		
		 
		global $wpdb;
		$kodi = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM '.$wpdb->prefix.'useriarkodiem'));
		echo '
		<div class="postbox" id="boxid" style="width:100%">
								 
								<h3 class="hndle"><span>Pieteiktie kodi</span></h3>
								<div class="inside">
		
		<table style="width:100%" class="widefat fixed" cellspacing="0">';
		echo "<thead> <tr>";
				echo '<th id="id" class="manage-column column-id" scope="col">ID </th>';
				echo '<th id="kods" class="manage-column column-kods" scope="col"> Pieteiktais kods </th>';
				echo '<th id="cena" class="manage-column column-cena" scope="col">Cena</th>';
				echo '<th id="ts" class="manage-column column-ts" scope="col">Laikspiedogs</th>';
				echo '<th id="ip" class="manage-column column-ip" scope="col">IP</th>';
				echo '<th id="status" class="manage-column column-status" scope="col">Status</th>';
				 
			
			
			echo "</tr></thead><tbody>";
		foreach ($kodi as $kt){
			
			echo "<tr>";
				echo "<td class='column-id'> ".$kt->id."</td>";
				echo "<td class='column-kods'> ".$kt->kods."</td>";
				echo "<td class='column-cena'> ".number_format( $kt->cena * 0.01, 2, '.', ' ') ."</td>";
				echo "<td class='column-ts'> ".$kt->ts."</td>";
				echo "<td class='column-ip'> ".$kt->ip."</td>";
				echo "<td class='column-status'> ".(($kt->status) == 1 ? '<p class="success"> Veiksmīgi </p> ':'<p class="danger"> Neizdevās </p')."</td>";
			
			
			echo "</tr>";
		}
		
		echo "</tbody></table></div>
					</div>";
		
		
	}
	function smspcont_contents(){
		
		 
		wp_enqueue_style('smspcont_stils');
		 global $wpdb; // JO nepieciešams mysql
		 echo "<div class='header'> </div>"; 
		echo "<table>";
			echo "<tr>";
			echo "<td style='width:30%'>";
				echo'
				<div class="postbox" id="boxid">
								 <center>
								<h3 class="hndle"><span>Jauna satura forma</span></h3>
								<div class="inside">
									 <form method="post">
									 <table>
										<tr> 
											<td> <br> Apraksts: </td> <td> <br><textarea  class="form-control" name="desc" placeholder="Apraksts"></textarea></td> 
										</tr>
										<tr> 
											<td> <br>Cena (3.00 -> 300) :  </td> <td><br><input class="form-control" type="number" name="price" placeholder="Cena"></td> 
										</tr>
										<tr> 
											<td><br>  Type:    </td> <td><br><select class="form-control" name="type">
											<option value=1>Do shortcode</option>
											<option value=2>Bilde</option>
											<option value=3>Atver adresi</option>
											<option value=4>Parāda tekstu</option>
										</select></td> 
										</tr>
										<tr> 
											<td><br>  Vērtība(shortcode, bildes adrese utt.):  </td> <td><br><input class="form-control" type="text" name="val" placeholder="Vērtība"></td> 
										</tr>
										<tr> 
										'.wp_nonce_field( 'shortcodeadd', 'slepenanonceaddshortc' ).'
											<td colspan=2> <br><input type="submit"  class="button action" name="newform" value="Saglabāt"> </td> 
										</tr>
									 </table>
									 
									 
									 
										 
										 
									 </form>
									 ';
					if (isset($_POST['newform'])){
								$desk = sanitize_text_field($_POST['desc']); // CHECHING HERE!!!!! NOT NEEDED TO CHECK AGAIN LATER!!
								$cena = sanitize_text_field($_POST['price']);
								$tips = sanitize_text_field($_POST['type']);
								$vertiba = sanitize_text_field($_POST['val']);
								if ($cena < 1){
									
									echo "<div class='denied'> Neapreiza cena </div>";
								}else if (!$desk ){
								
									echo "<div class='denied'> Nepieciešams apraksts </div>";
								
								}else if ( (!$cena) || !is_numeric($cena) || is_float($cena)){
								
									echo "<div class='denied'> Nepieciešama cena </div>";
								
								}else if (!$tips ){
								
									echo "<div class='denied'> Nepieciešams tips </div>";
								
								}else if (!$vertiba ){
								
									echo "<div class='denied'> Nepieciešams vertiba laukam </div>";
								
								}else{
									
									if (  ! isset( $_POST['slepenanonceaddshortc'] )  || ! wp_verify_nonce( $_POST['slepenanonceaddshortc'], 'shortcodeadd' )  ) {

										echo "<div class='denied'>Nekad.</div>";
										exit;

									} else {
										$irieraksts = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM '.$wpdb->prefix.'itapforms WHERE desk='$desk' and price='$cena' and type='$tips' and value='$vertiba' "));
										if ($irieraksts > 0){
											echo "<div class='denied'> Tāds ieraksts jau eksistē </div>";
										}else{
											$wpdb->query( $wpdb->prepare( "INSERT INTO `".$wpdb->prefix."itapforms`(`desk`, `price`, `type`, `value`) VALUES ('".$desk."','".$cena."','".$tips."','".$vertiba."')")); 
											echo "<div class='accesable'> Forma izveidota </div>";
										}
										
									}
										 
									
										 
							
								}			
						 		
					 }						 
				 echo ' </div></center>
					</div>';
				
				echo "</td>";
									if (isset($_POST['deleteform'])){
										if (!is_numeric($_POST['id'])){
											echo "<div class='denied'> Neatrad id! </div>";
										}else{
											if (  ! isset( $_POST['deletenonce'] )  || ! wp_verify_nonce( $_POST['deletenonce'], 'deleteshortcode' )  ) {

												echo "<div class='denied'>Nekad.</div>";
												exit;

											} else {
													$wpdb->query( $wpdb->prepare( "delete from `".$wpdb->prefix."itapforms` where id='".sanitize_text_field($_POST['id'])."' ")); 
											}
										}
									}
				$contenti = $wpdb->get_results('SELECT * FROM '.$wpdb->prefix.'itapforms'); 
		 
				 echo "<td style='width:70%'>";
				echo '<div class="postbox" id="boxid">
								<div title="Click to toggle" class="handlediv"><br></div>
								<h3 class="hndle"><span>New content</span></h3>
								<div class="inside">
									<table class="wp-list-table widefat fixed striped  ">
										 <tr>
											   
											<th>Apraksts</th>  
											<th>Cena</th>  
											<th>Tips</th>  
											<th>Vērtība</th>  
											<th>Shortcode</th>  
											<th>*</th>  
										 </tr>
									';
								 
									foreach ($contenti as $c ){
										echo "
										<tr>
											<td> ".$c->desk." </td> 
											<td> " . number_format( $c->price * 0.01, 2, '.', ' ') . " euro  </td> 
											<td> ".smspcont_veidi($c->type)." </td> 
											<td> ".$c->value." </td> 
											<td> <b> [itapcontent id='".$c->id."'] </b></td> 
											<td> 
											<form method='post'>
											".wp_nonce_field( 'deleteshortcode', 'deletenonce' )."
											<input type='number' name='id' value='".$c->id."' style='display:none'>
											<input type='submit'  class='button action' name='deleteform' value='Delete'>
											</form>
											
											</td> 
										 </tr>
										
										";
									}
									
									echo'  
									</table>
								</div>
					</div>';
				
				echo "</td>";
			echo "</tr>";
		
		
		
		echo "</table>";
	 
		 
	} 
	function smspcont_veidi ($a){
		switch ($a){
			case 1:
				return 'Shortcode';
			break;
			case 2:
				return 'Show image';
			break;
			case 3:
				return 'Open url';
			break;
			case 4:
				return 'Show text';
			break;
			default :
			return 'FAIL';
			break;
		}
		
		
	}
?>
