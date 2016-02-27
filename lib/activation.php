<?php
if ( ! defined( 'ABSPATH' ) ) die();

namespace OrionRush\DuplicateDetector\Activation;

if (get_option('orionrush_duplicate_detector') === false) {
    add_option('orionrush_duplicate_detector', \OrionRush\DuplicateDetector\Admin\get_defaults());
}