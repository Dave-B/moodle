<?php if (strpos($CFG->wwwroot, 'openmoodle.conted.ox.ac.uk') && $COURSE->shortname == 'CDE') { ?>

    <p class="copyright oxwidthnormal">© The University of Oxford. <a target="cc-by-nc-sa" href="http://creativecommons.org/licenses/by-nc-sa/3.0/" rel="license"><img src="http://i.creativecommons.org/l/by-nc-sa/3.0/80x15.png" style="border-width: 0pt;" alt="Creative Commons License" /></a><br /><span xmlns:dct="http://purl.org/dc/terms/" property="dct:title"><i><?php echo $COURSE->fullname ?></i></span>, excluding University of Oxford logos, by <a xmlns:cc="http://creativecommons.org/ns#" target="tall" rel="cc:attributionURL" property="cc:attributionName" href="http://www.tall.ox.ac.uk/">TALL</a> is<br />licensed under a <a target="cc-by-nc-sa" href="http://creativecommons.org/licenses/by-nc-sa/3.0/" rel="license">Creative Commons Attribution-NonCommercial-ShareAlike 3.0 Unported License</a>.</p>

<?php } else { ?>

     <p class="copyright"><a href="/copyright.php" target="Copyrightstatement_popup">© Copyright the University of Oxford and contributors.</a> <a href="/privacy.php">Privacy and cookies policy</a></p>

<?php } ?>
