<div id="allowConversation">
    <article>
        <?php _e('Allow Conversation', 'lms-conversation'); ?> : 
    </article>

    <label class="switch">
        <input type="checkbox" 
            name="allow_conversation"
            value='1'
            <?php esc_attr_e( get_post_meta( $post->ID, 'allow_conversation', true ) ? 'checked':'' ); ?>
        />
        <span class="slider"></span>
    </label> 
</div>


<style>
    #allowConversation{
        display: flex;
        align-items: center;
    }
    #allowConversation article{
        flex: 1;
    }
    /* The switch - the box around the slider */
    .switch {
        position: relative;
        display: inline-block;
        width: 60px;
        height: 34px;
    }
    
    /* Hide default HTML checkbox */
    .switch input {
        opacity: 0;
        width: 0;
        height: 0;
    }
    
    /* The slider */
    .slider {
        position: absolute;
        cursor: pointer;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ccc;
        -webkit-transition: .4s;
        transition: .4s;
    }
    
    .slider:before {
        position: absolute;
        content: "";
        height: 26px;
        width: 26px;
        left: 4px;
        bottom: 4px;
        background-color: white;
        -webkit-transition: .4s;
        transition: .4s;
    }
    
    .switch input:checked + .slider, 
    .switch input:checked + .form_group {
        background-color: #FF5670;
    }
    
    .switch input:focus + .slider {
        // box-shadow: 0 0 1px #2196F3;
    }
    
    input:checked + .slider:before {
        -webkit-transform: translateX(26px);
        -ms-transform: translateX(26px);
        transform: translateX(26px);
    }
    
    /* Rounded sliders */
    .slider {
        border-radius: 34px;
    }
    
    .slider:before {
        border-radius: 50%;
    }
</style>