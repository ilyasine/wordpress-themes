<div class="nl-body pleinevie">
	<div id="nl_body">
		<h1 class="text-center">Inscription newsletter</h1>
		<h3 class="text-center page-sub-title"> Inscrivez-vous et recevez gratuitement le meilleur de l'actualité de Pleine vie dans votre boîte mail.</h3>
		<form action="" method="post" class="form-horizontal" id="signupForm_nl">
			<div class="offers">
				<div class="offers_item">
					<input type="checkbox" value="pleinevie_optin_edito" class="offer_checkbox" id="choice-1" class="offer_checkbox" name="OPTIN_CHECKBOX" />
					<label for="choice-1"></label>
					<span class="check-box"></span>
					<span>La newsletter Pleine Vie : ne ratez rien de notre actualité</span>
					
					<ul class="list-unstyled list-inline pull-right offers_const">
						<li class="offers_frequancy">7j/7</li>
						<li class="offers_time">8h</li>
					</ul>
				</div>

				<div class="offers_item">
					<input type="checkbox" value="pleinevie_optin_part" id="choice-3" class="offer_checkbox" class="offer_checkbox" name="PART_CHECKBOX" />
					<label for="choice-3"></label>
					<span class="check-box"></span>
					<span> Recevoir les bons plans des partenaires de Pleine Vie</span>
				</div>
			</div>
			<div class="row sign-form">
				<h2 class="text-center page-title">Recevoir mon guide gratuitement</h2>
				<div class="col-xs-12 col-md-8 col-md-offset-2">
					<label for="email">Adresse mail*</label>
					<div class="row">		
						<div class="col-sm-9 col-xs-7 pr-0">
							<div class="form-group">
								<input type="email" required name="MAIL" checked="checked" <?php echo (isset($_GET["email_newsletter"]) && $_GET["email_newsletter"]) ? "value=".$_GET["email_newsletter"] : ''; ?> class="form-control" placeholder="Adresse mail" id="email_nl"/>
							</div>
						</div>
						<div class="col-sm-3 col-xs-5 pl-0">
							<button type="submit" class="btn btn-default">Je m'inscris</button>
						</div>
					</div>
				</div>
			</div>
			<!-- <label tabindex="0" class="radio" for="193_1">Oui</label> -->
		</form>
		<div class="nl-feature">
			<h2 class="text-center page-title">Nos engagements</h2>
			<div class="row">
				<div class="col-xs-6 col-md-3 nl-feature-item">
					<span class="book-icon"></span>
					Vous offrir gratuitement nos contenus exclusifs
				</div>
				<div class="col-xs-6 col-md-3 nl-feature-item">
					<span class="folder-icon"></span>
					Vous permettre d'accéder aux dossiers de la rédac'
				</div>
				<div class="col-xs-6 col-md-3 nl-feature-item">
					<span class="msg-icon"></span>
					Vous accompagner tout au long de votre parcours
				</div>
				<div class="col-xs-6 col-md-3 nl-feature-item">
					<span class="lock-icon"></span>
					Respecter vos données personnelles
				</div>
			</div>
		</div>
	</div>
	<div id="confirmation-old" style="display: none">
		<h2 class="text-center page-title">Merci !</h2>
		<h3 class="text-center page-sub-title">Nous avons bien pris en compte votre demande pour l'adresse MAIL_NL<br /><br>À très vite sur pleinevie !</h3>
	</div>

	<div id="confirmation-new" style="display: none">
		<h2 class="text-center page-title">Merci !</h2>
		<h3 class="text-center page-sub-title">Nous avons bien pris en compte votre demande pour l'adresse MAIL_NL<br>
			Vous venez de recevoir un <strong>mail de confirmation d'inscription</strong> (n'oubliez pas de regarder dans vos courriers indésirables).<br>
			<br>
		À très vite sur pleinevie !</h3>
	</div>
</div>





