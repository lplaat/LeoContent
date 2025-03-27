<?php

use Src\App\Models\Content;

include '../config.php';

include '../components/header.php';
?>

<div class="px-2">
    <h3>Home</h1>
    
    <div>
        <?php 
            $contents = Content::whereIn('type', [1, 2])->where('is_prepared', true)->get();
            foreach($contents as $content) {
                include '../components/card.php';
            }
        ?>
    </div>
</div>

<?php
include '../components/footer.php';