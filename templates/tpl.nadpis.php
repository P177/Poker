<?php
if (AGet($_GET,'action') == "komentar" || AGet($_GET,'action') == "send"){echo "<span style=\"color = #FFCC00\">Koment�r k cl�nku:</span> ".$article_headline;}
if (AGet($_GET,'action') == "clanek"){echo "<span style=\"color = #FFCC00\">Cl�nek:</span> ".$article_headline;}
?>