<?php get_header(); ?>
 
 <div class="header-AlloPV zone-full">
 
     <div class="container header-AlloPV_intro">
         <img class="lazy" src= "<?php echo STYLESHEET_DIR_URI . '/pleinevie/assets/images/allo-pleinevie/logo.png' ?>" alt=""> 
         <p class="header-AlloPV_intro__text">
             Pleine Vie vous propose un
             nouveau service d’assistance
             et de dépannage informatique
             en illimité par téléphone !
         </p>
         <a href="tel:<?= $telephone ?>" class="alloPV__btn">
            Contactez le service au <span class="alloPV_tele"> <?= $telephone ?> </span>
         </a> 
     </div>
 
 </div>
 
 <section class="container clearfix info-AlloPV">
     <div class="info-AlloPV__text">
        <h2 class="info-AlloPV__title">Comment ça marche ?</h2>
        <p>Quel que soit le problème rencontré, nous réalisons un <span> diagnostic gratuit</span> pour vous conseiller sur la meilleure résolution de <span>votre dépannage.</span></p>
        <p>Vous pouvez choisir entre un <span>dépannage ponctuel</span> ou <span>l’activation d’un forfait d’assistance illimitée</span> pour être accompagné dans la durée en toute sérénité et à très bas prix.</p>
        <p>Tarifs forfaitaires et non au temps passé pour une maîtrise de <span>votre budget à 100%.</span></p>
    </div>
     <div class="info-AlloPV__img">
         <img class="lazy" src="<?php echo STYLESHEET_DIR_URI . '/pleinevie/assets/images/allo-pleinevie/service.png' ?>" class="info-AlloPV__img"> 
     </div>
 </section>

 <section class="propositions-AlloPV zone-full">
     <div class="container">
         <h3 class="block-title">Nous vous proposons deux offres</h3>  
 
         <div class="boxes-AlloPV">
             <div class="boxe-AlloPV">
                 <div class="boxe-AlloPV__icon"> </div>
                 <div class="boxe-AlloPV__text">
                     <h4>  Nos dépannages ponctuels dès 29,90€ TTC </h4>
                     <p>Un problème passager à résoudre ? découvrez le fonctionnement de nos dépannages ponctuels, garantis dépanné ou remboursé et au tarif fixe.</p>
                 </div>
             </div>
             <div class="boxe-AlloPV">
                 <div class="boxe-AlloPV__icon"> </div>
                 <div class="boxe-AlloPV__text">
                     <h4>Nos forfaits d’assistance illimitée dès 11,90€ TTC </h4> 
                     <p> Dépannage, assistance et conseils en illimité, dès 11,90€ TTC par mois ! Appelez maintenant pour vous abonner. </p>
                 </div>
             </div>
         </div>
     </div>  
 </section>
 
 <section class="contactez-nous-AlloPV zone-full" >
     <div class="container">
         <p> 
             Contactez-nous dès maintenant</br> 
             <span> du Lundi au Samedi de 9h à 21h au :<span>  
         </p>
         <a href="tel:<?= $telephone ?> " class="alloPV__btn">
            <span class="alloPV_tele"> <?= $telephone ?>  </span>
         </a> 
     </div>
 </section>
 
 <section class="moment-AlloPV">
     <div class="container">
         <h3 class="block-title">A quel moment nous contacter ?</h3>  
 
         <div class="boxes-AlloPV">
             <div class="boxe-AlloPV">
                 <div class="boxe-AlloPV__icon"> </div>
                 <div class="boxe-AlloPV__text">
                     <p>Vous avez besoin d’aide pour installer un logiciel ou un équipement</p>
                 </div>
             </div>
             <div class="boxe-AlloPV">
                 <div class="boxe-AlloPV__icon"> </div>
                 <div class="boxe-AlloPV__text">
                     <p>Vous avez des problèmes de connexion internet </p>
                 </div>
             </div>
             <div class="boxe-AlloPV">
                 <div class="boxe-AlloPV__icon"> </div>
                 <div class="boxe-AlloPV__text">
                     <p>Votre ordinateur est lent ou bloqué</p>
                 </div>
             </div>
             <div class="boxe-AlloPV">
                 <div class="boxe-AlloPV__icon"> </div>
                 <div class="boxe-AlloPV__text">
                     <p>Vous avez perdu vos données et vous souhaitez les récupérer </p>
                 </div>
             </div>
             <div class="boxe-AlloPV">
                 <div class="boxe-AlloPV__icon"> </div>
                 <div class="boxe-AlloPV__text">
                     <p>Vous avez un problème avec vos emails</p>
                 </div>
             </div>
             <div class="boxe-AlloPV">
                 <div class="boxe-AlloPV__icon"> </div>
                 <div class="boxe-AlloPV__text">
                     <p>Vous avez un virus sur votre ordinateur</p>
                 </div>
             </div>
         </div>
     </div>  
 </section>
<?php get_footer(); ?>