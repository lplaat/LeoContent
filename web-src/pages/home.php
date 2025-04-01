<?php

use Src\App\Models\Content;

include '../config.php';

$contents = Content::whereIn('type', [1, 2])->where('is_prepared', true)->get();

if($contents->count() !== 0) {
    $transparentNav = true;
}

include '../components/header.php';
?>

<div class="position-absolute w-100">
    <?php 
        if($contents->count() !== 0) {
            include '../components/preview.php';
        }
    ?>

    <div class="px-4 mt-2 w-100">  
        <h2>Shows</h2>      
        <div>
            <?php 
                $contents = Content::whereIn('type', [2])->where('is_prepared', true)->get();

                foreach($contents as $content) {
                    include '../components/card.php';
                }
            ?>
        </div>

        <h2>Movies</h2>
        <div>
            <?php 
                $contents = Content::whereIn('type', [1])->where('is_prepared', true)->get();
            
                foreach($contents as $content) {
                    include '../components/card.php';
                }
            ?>
        </div>
    </div>
</div>

<?php
include '../components/footer.php';