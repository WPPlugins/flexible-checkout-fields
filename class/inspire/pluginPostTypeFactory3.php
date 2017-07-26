<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
	
	if (!class_exists('inspire_pluginPostTypeFactory3'))
	{

	    abstract class inspire_pluginPostTypeFactory3 extends inspire_pluginDependant3
	    {  
	    	protected $_postType = '';
	    	protected $_postTypeArray = array();
	    	protected $_metaboxes = array();
	    	protected $_prefix = "_inspire_";
	    	protected $_plugin;
	    	
	    			
	    	public function __construct(inspire_Plugin3 $plugin)
	    	{
	    		$this->_plugin = $plugin;
	    		
	    		add_action( 'init', array($this, 'initPostTypeAction'), 0 );
	    			
	    		add_action( 'add_meta_boxes', array($this, 'createCustomFieldsAction' ), 1, 2 );
	    		add_action( 'save_post', array( $this, 'saveCustomFieldsAction' ), 1, 2 );
	    		
	    		// columns 
	    		add_filter( 'manage_edit-' . $this->getPostTypeSlug() . '_columns', array($this, 'addCustomColumnsFilter') );
	    		add_action( 'manage_' . $this->getPostTypeSlug() . '_posts_custom_column', array($this, 'displayCustomColumnFilter'), 10, 2);
	    		
	    		// Comment this line out if you want to keep default custom fields meta box
	    		//add_action( 'do_meta_boxes', array($this, 'removeDefaultCustomFields' ), 10, 3 );
	    	}
	    	
	    	public function getPostTypeArray()
	    	{
	    		return $this->_postTypeArray;
	    	}
	    	
	    	public function getMetaboxesArray()
	    	{
	    		return $this->_metaboxes;
	    	}
	    	
	    	public function getPostTypeSlug()
	    	{
	    		return $this->_postType;
	    	}
	    	
	    	public function addCustomColumnsFilter($gallery_columns)
	    	{
	    		foreach ($this->getMetaboxesArray() as $metabox)
	    		{
	    			foreach ($metabox['fields'] as $field)
	    			{
	    				if (!empty($field['column']))
	    				{
	    					$gallery_columns[$field['name']] = $field['column'];
	    				}
	    			}
	    		}
	    		
	    		return $gallery_columns;
	    	}
	    	
	    	public function displayCustomColumnFilter($column_name, $post_id)
	    	{
	    		echo get_post_meta( $post_id, $this->_prefix . $column_name, true );
	    	}
	    	
	    	public function initPostTypeAction()
	    	{
	    		register_post_type( $this->getPostTypeSlug(), $this->getPostTypeArray() );
	    	}
	    	
	    	/*public function getLabelForFieldOptionValue($metaboxId, $fieldName, $optionValue)
	    	{
	    		$metaboxes = $this->getMetaboxesArray();
	    		foreach ($metaboxes as $metabox)
	    		{
	    			if ($metaboxId == $metabox['id'])
	    			{
	    				foreach ($metabox['fields'] as $field)
	    				{
	    					if ($field['name'] == $fieldName && $field['type'] == 'select')
	    					{
	    						foreach ($field['options'] as $name => $value)
	    						{
	    							if ($optionValue == $value)
	    							{
	    								return $name;
	    							}
	    						}
	    					}
	    				}
	    			}
	    		}
	    	}*/
	    	
	    	/**
	    	 * Remove the default Custom Fields meta box
	    	 */
	    	function removeDefaultCustomFields( $type, $context, $post ) {
	    		foreach ( array( 'normal', 'advanced', 'side' ) as $context ) {
	    			remove_meta_box( 'postcustom', $this->getPostTypeSlug(), $context );
	    			/*foreach ( $this->postTypes as $postType ) {
	    			 remove_meta_box( 'postcustom', $postType, $context );
	    			}*/
	    		}
	    	}
	    	/**
	    	 * Create the new Custom Fields meta box
	    	 */
	    	public function createCustomFieldsAction($post_type, $post) {
	    		if ( function_exists( 'add_meta_box' ) )
	    		{
					foreach ($this->getMetaboxesArray() as $metabox)
					{
	    				add_meta_box( $metabox['id'], $metabox['name'], array( $this, 'displayCustomFieldsAction' ), $this->getPostTypeSlug(), 'normal', 'high', array('metabox' => $metabox) );
					}
	    		}
	    	}
	    	
	    	/**
	    	 * Display the new Custom Fields meta box
	    	 */
	    	public function displayCustomFieldsAction(WP_Post $post, $boxArray) {
	    		$output = true;
	    		?>
    	            <div class="form-wrap">
    	                <?php
    	                wp_nonce_field( 'my-custom-fields', 'my-custom-fields_wpnonce', false, true );
    	                	foreach ($boxArray['args']['metabox']['fields'] as $customField)
    	                	{
    	
    	                        
    	                    // Check capability
    	                    /*if ( !current_user_can( $customField['capability'], $post->ID ) )
    	                        $output = false;*/
    	                    // Output if allowed
    	                    if ( $output ) { ?>
    	                        <div class="form-field form-required">
    	                            <?php
    	                            switch ( $customField[ 'type' ] ) {
    	                                case "checkbox": {
    	                                    // Checkbox
    	                                    echo '<label for="' . $this->_prefix . $customField[ 'name' ] .'" style="display:inline;"><b>' . $customField[ 'title' ] . '</b></label>&nbsp;&nbsp;';
    	                                    echo '<input type="checkbox" name="' . $this->_prefix . $customField['name'] . '" id="' . $this->_prefix . $customField['name'] . '" value="yes"';
    	                                    if ( get_post_meta( $post->ID, $this->_prefix . $customField['name'], true ) == "yes" )
    	                                        echo ' checked="checked"';
    	                                    echo '" style="width: auto;" />';
    	                                    break;
    	                                }
    	                                case "select":
    	                                	echo '<label for="' . $this->_prefix . $customField[ 'name' ] .'"><b>' . $customField[ 'title' ] . '</b></label>';
    	                                	echo '<select name="' . $this->_prefix . $customField[ 'name' ] . '" id="' . $this->_prefix . $customField[ 'name' ] . '" >';
    	                                	foreach ($customField['options'] as $label => $value)
    	                                	{
    	                                		echo '<option value="' . $value . '" ' . ((get_post_meta( $post->ID, $this->_prefix . $customField[ 'name' ], true ) == $value)? 'selected="selected"': '') . '>' . $label . '</option>';
    	                                	}
    	                                	echo '</select>';
    	                                break;
    	                                
    	                                case "textarea":
    	                                case "wysiwyg": {
    	                                    // Text area
    	                                    echo '<label for="' . $this->_prefix . $customField[ 'name' ] .'"><b>' . $customField[ 'title' ] . '</b></label>';
    	                                    echo '<textarea name="' . $this->_prefix . $customField[ 'name' ] . '" id="' . $this->_prefix . $customField[ 'name' ] . '" columns="30" rows="3">' . htmlspecialchars( get_post_meta( $post->ID, $this->_prefix . $customField[ 'name' ], true ) ) . '</textarea>';
    	                                    // WYSIWYG
    	                                    if ( $customField[ 'type' ] == "wysiwyg" ) { ?>
    	                                        <script type="text/javascript">
    	                                            jQuery( document ).ready( function() {
    	                                                jQuery( "<?php echo $this->_prefix . $customField[ 'name' ]; ?>" ).addClass( "mceEditor" );
    	                                                if ( typeof( tinyMCE ) == "object" && typeof( tinyMCE.execCommand ) == "function" ) {
    	                                                    tinyMCE.execCommand( "mceAddControl", false, "<?php echo $this->_prefix . $customField[ 'name' ]; ?>" );
    	                                                }
    	                                            });
    	                                        </script>
    	                                    <?php }
    	                                    break;
    	                                }
    	                                default: {
    	                                    // Plain text field
    	                                    echo '<label for="' . $this->_prefix . $customField[ 'name' ] .'"><b>' . $customField[ 'title' ] . '</b></label>';
    	                                    echo '<input type="text" name="' . $this->_prefix . $customField[ 'name' ] . '" id="' . $this->_prefix . $customField[ 'name' ] . '" value="' . htmlspecialchars( get_post_meta( $post->ID, $this->_prefix . $customField[ 'name' ], true ) ) . '" />';
    	                                    break;
    	                                }
    	                            }
    	                            ?>
    	                            <?php if ( $customField[ 'description' ] ) echo '<p>' . $customField[ 'description' ] . '</p>'; ?>
    	                        </div>
    	                    <?php
    	                    }
    	                } ?>
    	            </div>
    	            <?php
    	        }
    	        /**
    	        * Save the new Custom Fields values
    	        */
    	        public function saveCustomFieldsAction( $post_id, $post ) {
    	            if ( !isset( $_POST[ 'my-custom-fields_wpnonce' ] ) || !wp_verify_nonce( $_POST[ 'my-custom-fields_wpnonce' ], 'my-custom-fields' ) )
    	                return;
    	            /*if ( !current_user_can( 'edit_post', $post_id ) )
    	                return; */
    	            
    	            foreach ($this->getMetaboxesArray() as $metabox)
    	            {
	    	            foreach ( $metabox['fields'] as $customField ) 
	    	            {
	    	                //if ( current_user_can( $customField['capability'], $post_id ) ) 
	    	                {
	    	                    if ( isset( $_POST[ $this->_prefix . $customField['name'] ] ) && trim( $_POST[ $this->_prefix . $customField['name'] ] ) ) {
	    	                        $value = $_POST[ $this->_prefix . $customField['name'] ];
	    	                        // Auto-paragraphs for any WYSIWYG
	    	                        if ( $customField['type'] == "wysiwyg" ) $value = wpautop( $value );
	    	                        update_post_meta( $post_id, $this->_prefix . $customField[ 'name' ], $value );
	    	                    } else {
	    	                        delete_post_meta( $post_id, $this->_prefix . $customField[ 'name' ] );
	    	                    }
	    	                }
	    	            }
    	            }
    	        }
    	        
    	       
	    	
	    }
	}
