<?php 
    $current_user_id = get_current_user_id(); // Get the current user's ID

    $args = array(
        'post_type' => 'virtual_ai_render',
        'meta_query' => array(
            array(
                'key' => 'user_id',
                'value' => $current_user_id,
                'compare' => '='
            )
        )
    );

$query = new WP_Query($args);



?>

<div class="container px-lg-5">

    <?php
        $api_key = 'SADS';

        if($api_key != ""):
    ?>
    <div class="row my-lg-5 my-4 d-none" id="ImageData">
        <div class="d-flex my-2">
            <a href="" class="btn btn-primary px-4" download="renderedImage.jpg" id="downloadImage">Download</a>
        </div>
        <p>Please right click to save image as</p>
        <img src="" alt="" class="img-fluid my-3" id="imagePort">

    </div>
    <div class="row my-lg-5 my-3">

        <form id="virtualForm" action="" enctype="multipart/form-data">
            
            <h2 class="my-3">Create a render</h2>
            <?php wp_nonce_field("wp_rest") ?>
            <input type="hidden" name="user_id" value="<?php echo get_current_user_id(); ?>">
            <div class="my-3">
               <label for="formFile" class="form-label">Select an image (Max size 1MB)</label>
                <input id="fileData" class="form-control" type="file" name="image_file" id="formFile" accept="image/*" required>
            </div>
            <div class="my-3">
                <select class="form-select" aria-label="Default select example" name="room_type" required>
                    <option value="">Room Type (The type of room in the uploaded photo.)</option>
                    <option value="living">Living Room</option>
                    <option value="bed">Bed</option>
                    <option value="kitchen">Kitchen</option>
                    <option value="dining">Dining</option>
                    <option value="bathroom">Bathroom</option>
                    <option value="home_office">Home Office</option>
                </select>
            </div>
            <div class="my-3">
                <select class="form-select" aria-label="Default select example" name="style" required>
                    <option value="">Style (The desired style of furnishing. Must be one of the following)</option>
                    <option value="standard">Standard (Best Results)</option>
                    <option value="modern">Modern</option>
                    <option value="scandinavian">Scandinavian</option>
                    <option value="industrial">Industrial</option>
                    <option value="mid-century">Mid-century</option>
                    <option value="coastal">Coastal</option>
                    <option value="american">American</option>
                    <option value="southwestern">Southwestern</option>
                    <option value="farmhouse">Farmhouse</option>
                    <option value="luxury">Luxury</option>
                </select>
            </div>
            <div class="my-3">
                <p class="mb-0" >Resolution</p>
                <div class="form-check">
                    <input class="form-check-input" type="radio" value="4k" name="resolution" id="flexRadioDefault1" checked>
                    <label class="form-check-label" for="flexRadioDefault1">
                        4k
                    </label>
                    </div>
                    <div class="form-check">
                    <input class="form-check-input" type="radio" value="full-hd" name="resolution" id="flexRadioDefault2" >
                    <label class="form-check-label" for="flexRadioDefault2">
                        Full-HD
                    </label>
                </div>
            </div>
            <div class="my-5">
                <button type="submit" id="renderButton" class=" d-flex justify-content-between align-items-center btn btn-primary px-4">
                    Render
                </button>
            </div>

        </form>
    </div>

    <div class="row">
        <div class="d-flex justify-content-between flex-lg-row flex-column align-items-center">
            <div class="">
                <h2 >
                    Rendered Images
                </h2 >
                <p class="mt-0">Please right click to save image as</p>
            </div>
            <div class="d-flex">
                <button class="btn btn-danger px-4 d-none"  id="deleteImages">Delete All</button>
            </div>
        </div>
    </div>
    <div class="row my-3 g-4" id="allImages">
        <?php 
        if ($query->have_posts()) :
            while ($query->have_posts()) :
                $query->the_post();
                $render_id = get_post_meta(get_the_ID(), 'render_id', true);
                $image_url = get_post_meta(get_the_ID(), 'result_image_url', true);
                // ...do something with the retrieved data

        ?>
        <div class="col-lg-4 col-md-6 col-12">
            <img src="<?php echo $image_url?>" id="<?php echo $render_id ?>" alt="" class="image-box">
            <div class="d-flex justify-content-between  align-items-center">
                <div>
                    <a href="<?php echo $image_url?>" class="btn btn-primary px-4 my-3 image-link" download="file.jpg" id="">Download</a>
                </div>
                <div>
                    <button data-id="<?php echo get_the_ID() ?>" class="btn btn-danger px-4 my-3 delete-link"  id=""><i class="bi bi-trash"></i></button>
                </div>
            </div>
        </div>
        
        <?php endwhile; ?>
        <?php  wp_reset_postdata(); // Reset the post data ?>
        <?php  else:  ?>
            <h3>No renders found....</h3>
        <?php  endif;  ?>
        
    </div>
    <?php else: ?>
        <div class="row my-5">
            <h4 class="text-center">
                Virtual Staging API key hasn't been set!!
            </h4>
        </div>
    <?php endif; ?>

</div>
