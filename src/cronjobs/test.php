<?php

use Src\App\Models\Content;

include __DIR__ . '/../config.php';

Content::find(4)->prepare();