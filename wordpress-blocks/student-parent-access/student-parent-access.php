<?php
/**
 * Plugin Name: Student Parent Access Control
 * Plugin URI: https://yoursite.com
 * Description: Manages student and parent roles with category/tag-based content access control. Parents can assign categories and tags to students to control what content they can view and comment on.
 * Version: 1.0.0
 * Author: Travis ralph
 * Author URI: https://yoursite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: student-parent-access
 * Requires at least: 5.0
 * Requires PHP: 7.2
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('SPA_VERSION', '1.0.0');
define('SPA_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('SPA_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * ========================================
 * ACTIVATION & DEACTIVATION HOOKS
 * ========================================
 */

/**
 * Plugin activation - create roles once
 */
function spa_activate_plugin() {
    // Create Student Role (same as Subscriber)
    $subscriber = get_role('subscriber');
    if ($subscriber && !get_role('student')) {
        add_role('student', 'Student', $subscriber->capabilities);
    }
    
    // Create Parent Role (Author + custom capabilities)
    $author = get_role('author');
    if ($author && !get_role('parent')) {
        $parent_caps = $author->capabilities;
        $parent_caps['manage_students'] = true;
        $parent_caps['assign_content_to_students'] = true;
        $parent_caps['view_student_activity'] = true;
        add_role('parent', 'Parent', $parent_caps);
    }
    
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'spa_activate_plugin');

/**
 * Plugin deactivation
 */
function spa_deactivate_plugin() {
    // Don't remove roles on deactivation to preserve user data
    // If you want to remove roles, uncomment the lines below
    // remove_role('student');
    // remove_role('parent');
    
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'spa_deactivate_plugin');


/**
 * ========================================
 * ADMIN MENU & INTERFACE
 * ========================================
 */

/**
 * Add admin menu for Parents to manage student content access
 */
function spa_add_student_content_management_menu() {
    // For Parents
    if (current_user_can('manage_students')) {
        add_menu_page(
            'Manage Students',
            'My Students',
            'manage_students',
            'manage-student-content',
            'spa_render_student_content_management_page',
            'dashicons-groups',
            30
        );
    }
}
add_action('admin_menu', 'spa_add_student_content_management_menu');

/**
 * Render the student content management page for Parents
 */
function spa_render_student_content_management_page() {
    if (!current_user_can('manage_students')) {
        wp_die('You do not have permission to access this page.');
    }
    
    // Handle form submission
    if (isset($_POST['save_student_access']) && check_admin_referer('student_access_action', 'student_access_nonce')) {
        spa_save_student_content_access();
        echo '<div class="notice notice-success is-dismissible"><p><strong>Success!</strong> Student access settings have been saved.</p></div>';
    }
    
    // Get parent's assigned students
    $parent_id = get_current_user_id();
    $assigned_students = get_user_meta($parent_id, 'assigned_students', true);
    $student_ids = $assigned_students ? $assigned_students : [];
    $students = !empty($student_ids) ? get_users(['include' => $student_ids]) : [];
    
    ?>
    <div class="wrap">
        <h1><span class="dashicons dashicons-groups"></span> Manage Student Content Access</h1>
        
        <?php if (empty($students)) : ?>
            <div class="notice notice-warning">
                <p><strong>No students assigned.</strong> Please contact an administrator to assign students to your account.</p>
            </div>
        <?php else : ?>
            
            <div class="notice notice-info">
                <p><strong>Instructions:</strong> Select the categories and tags that each student can access. Students will only see posts that match their assigned categories or tags. Hold Ctrl (Windows) or Cmd (Mac) to select multiple items.</p>
            </div>
            
            <form method="post" action="">
                <?php wp_nonce_field('student_access_action', 'student_access_nonce'); ?>
                
                <table class="widefat" style="margin-top: 20px;">
                    <thead>
                        <tr>
                            <th style="width: 20%;">Student Name</th>
                            <th style="width: 25%;">Email</th>
                            <th style="width: 25%;">Allowed Categories</th>
                            <th style="width: 20%;">Allowed Tags</th>
                            <th style="width: 10%;">Can Comment</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student) : 
                            $allowed_categories = get_user_meta($student->ID, 'allowed_categories', true);
                            $allowed_categories = $allowed_categories ? $allowed_categories : [];
                            
                            $allowed_tags = get_user_meta($student->ID, 'allowed_tags', true);
                            $allowed_tags = $allowed_tags ? $allowed_tags : [];
                            
                            $can_comment = get_user_meta($student->ID, 'can_comment', true);
                        ?>
                            <tr>
                                <td><strong><?php echo esc_html($student->display_name); ?></strong></td>
                                <td><?php echo esc_html($student->user_email); ?></td>
                                <td>
                                    <?php
                                    $categories = get_categories(['hide_empty' => false]);
                                    if (!empty($categories)) {
                                        echo '<select name="student_categories[' . $student->ID . '][]" multiple style="width: 100%; height: 150px;">';
                                        foreach ($categories as $category) {
                                            $selected = in_array($category->term_id, $allowed_categories) ? 'selected' : '';
                                            echo '<option value="' . esc_attr($category->term_id) . '" ' . $selected . '>';
                                            echo esc_html($category->name) . ' (' . $category->count . ' posts)';
                                            echo '</option>';
                                        }
                                        echo '</select>';
                                        echo '<br><small style="color: #666;">Hold Ctrl/Cmd to select multiple</small>';
                                    } else {
                                        echo '<em>No categories available</em>';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php
                                    $tags = get_tags(['hide_empty' => false]);
                                    if (!empty($tags)) {
                                        echo '<select name="student_tags[' . $student->ID . '][]" multiple style="width: 100%; height: 150px;">';
                                        foreach ($tags as $tag) {
                                            $selected = in_array($tag->term_id, $allowed_tags) ? 'selected' : '';
                                            echo '<option value="' . esc_attr($tag->term_id) . '" ' . $selected . '>';
                                            echo esc_html($tag->name) . ' (' . $tag->count . ' posts)';
                                            echo '</option>';
                                        }
                                        echo '</select>';
                                        echo '<br><small style="color: #666;">Hold Ctrl/Cmd to select multiple</small>';
                                    } else {
                                        echo '<em>No tags available</em>';
                                    }
                                    ?>
                                </td>
                                <td style="text-align: center;">
                                    <label>
                                        <input type="checkbox" 
                                               name="student_comments[<?php echo $student->ID; ?>]" 
                                               value="1"
                                               <?php checked($can_comment, '1'); ?>>
                                        Allow
                                    </label>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p class="submit">
                    <input type="submit" name="save_student_access" class="button button-primary button-large" value="Save All Settings">
                </p>
            </form>
            
            <div class="card" style="margin-top: 30px; max-width: none;">
                <h2>How This Works</h2>
                <ul style="line-height: 1.8;">
                    <li><strong>Categories:</strong> Students will only see posts that belong to their assigned categories.</li>
                    <li><strong>Tags:</strong> Students will also see posts that have their assigned tags (in addition to category access).</li>
                    <li><strong>OR Logic:</strong> If a post has an allowed category OR an allowed tag, the student can see it.</li>
                    <li><strong>Comments:</strong> Control whether each student can leave comments on the content they can view.</li>
                    <li><strong>No Selection:</strong> If no categories or tags are selected, the student won't be able to view any content.</li>
                    <li><strong>Automatic Updates:</strong> When you add new posts to allowed categories/tags, students automatically gain access.</li>
                </ul>
            </div>
            
        <?php endif; ?>
    </div>
    
    <style>
        .wrap h1 .dashicons {
            font-size: 32px;
            width: 32px;
            height: 32px;
            vertical-align: middle;
            margin-right: 10px;
        }
        .widefat th {
            font-weight: 600;
            background: #f0f0f1;
        }
        .widefat td {
            padding: 15px 10px;
            vertical-align: top;
        }
    </style>
    <?php
}

/**
 * Save student content access settings
 */
function spa_save_student_content_access() {
    if (!current_user_can('manage_students')) {
        return;
    }
    
    $parent_id = get_current_user_id();
    $assigned_students = get_user_meta($parent_id, 'assigned_students', true);
    $student_ids = $assigned_students ? $assigned_students : [];
    
    foreach ($student_ids as $student_id) {
        // Save allowed categories
        if (isset($_POST['student_categories'][$student_id])) {
            $categories = array_map('intval', $_POST['student_categories'][$student_id]);
            update_user_meta($student_id, 'allowed_categories', $categories);
        } else {
            update_user_meta($student_id, 'allowed_categories', []);
        }
        
        // Save allowed tags
        if (isset($_POST['student_tags'][$student_id])) {
            $tags = array_map('intval', $_POST['student_tags'][$student_id]);
            update_user_meta($student_id, 'allowed_tags', $tags);
        } else {
            update_user_meta($student_id, 'allowed_tags', []);
        }
        
        // Save comment permission
        if (isset($_POST['student_comments'][$student_id])) {
            update_user_meta($student_id, 'can_comment', '1');
        } else {
            update_user_meta($student_id, 'can_comment', '0');
        }
    }
}


/**
 * ========================================
 * CONTENT FILTERING FOR STUDENTS
 * ========================================
 */

/**
 * Filter content visibility for students based on categories and tags
 */
function spa_filter_student_content_by_taxonomy($query) {
    // Only apply to main query, not admin, and for student users
    if (!$query->is_main_query() || is_admin()) {
        return;
    }
    
    $current_user = wp_get_current_user();
    
    if (!in_array('student', $current_user->roles)) {
        return;
    }
    
    // Get student's allowed categories and tags
    $allowed_categories = get_user_meta($current_user->ID, 'allowed_categories', true);
    $allowed_categories = $allowed_categories ? $allowed_categories : [];
    
    $allowed_tags = get_user_meta($current_user->ID, 'allowed_tags', true);
    $allowed_tags = $allowed_tags ? $allowed_tags : [];
    
    // If no categories or tags assigned, show nothing
    if (empty($allowed_categories) && empty($allowed_tags)) {
        $query->set('post__in', [0]); // Show no posts
        return;
    }
    
    // Build tax query for categories and tags
    $tax_query = ['relation' => 'OR'];
    
    if (!empty($allowed_categories)) {
        $tax_query[] = [
            'taxonomy' => 'category',
            'field'    => 'term_id',
            'terms'    => $allowed_categories,
            'operator' => 'IN'
        ];
    }
    
    if (!empty($allowed_tags)) {
        $tax_query[] = [
            'taxonomy' => 'post_tag',
            'field'    => 'term_id',
            'terms'    => $allowed_tags,
            'operator' => 'IN'
        ];
    }
    
    $query->set('tax_query', $tax_query);
}
add_action('pre_get_posts', 'spa_filter_student_content_by_taxonomy');

/**
 * Check if student can view a specific post
 */
function spa_can_student_view_post($post_id, $user_id = null) {
    if (!$user_id) {
        $user_id = get_current_user_id();
    }
    
    $user = get_userdata($user_id);
    
    // If not a student, allow access
    if (!in_array('student', $user->roles)) {
        return true;
    }
    
    // Get student's allowed categories and tags
    $allowed_categories = get_user_meta($user_id, 'allowed_categories', true);
    $allowed_categories = $allowed_categories ? $allowed_categories : [];
    
    $allowed_tags = get_user_meta($user_id, 'allowed_tags', true);
    $allowed_tags = $allowed_tags ? $allowed_tags : [];
    
    // If no categories or tags assigned, deny access
    if (empty($allowed_categories) && empty($allowed_tags)) {
        return false;
    }
    
    // Check if post has allowed categories
    $post_categories = wp_get_post_categories($post_id);
    if (!empty(array_intersect($post_categories, $allowed_categories))) {
        return true;
    }
    
    // Check if post has allowed tags
    $post_tags = wp_get_post_tags($post_id, ['fields' => 'ids']);
    if (!empty(array_intersect($post_tags, $allowed_tags))) {
        return true;
    }
    
    return false;
}

/**
 * Redirect students away from unauthorized single posts
 */
function spa_redirect_unauthorized_student_access() {
    if (!is_singular('post')) {
        return;
    }
    
    $current_user = wp_get_current_user();
    
    if (!in_array('student', $current_user->roles)) {
        return;
    }
    
    $post_id = get_the_ID();
    
    if (!spa_can_student_view_post($post_id)) {
        wp_redirect(home_url());
        exit;
    }
}
add_action('template_redirect', 'spa_redirect_unauthorized_student_access');

/**
 * Filter comment ability for students
 */
function spa_filter_student_comments($open, $post_id) {
    $current_user = wp_get_current_user();
    
    // If user is not a student, use default comment settings
    if (!in_array('student', $current_user->roles)) {
        return $open;
    }
    
    // Check if student can view this post
    if (!spa_can_student_view_post($post_id, $current_user->ID)) {
        return false;
    }
    
    // Check if student is allowed to comment
    $can_comment = get_user_meta($current_user->ID, 'can_comment', true);
    
    return $can_comment == '1' ? $open : false;
}
add_filter('comments_open', 'spa_filter_student_comments', 10, 2);


/**
 * ========================================
 * USER PROFILE FIELDS
 * ========================================
 */

/**
 * Add custom user profile fields to assign students to parents
 */
function spa_add_parent_student_relationship_fields($user) {
    if (!current_user_can('manage_options')) {
        return;
    }
    
    // For Parent users
    if (in_array('parent', $user->roles)) {
        $assigned_students = get_user_meta($user->ID, 'assigned_students', true);
        $assigned_students = $assigned_students ? $assigned_students : [];
        
        $all_students = get_users(['role' => 'student']);
        
        ?>
        <h2>Parent Settings</h2>
        <table class="form-table" role="presentation">
            <tr>
                <th><label>Assigned Students</label></th>
                <td>
                    <?php if (empty($all_students)) : ?>
                        <p><em>No students available. Create student accounts first.</em></p>
                    <?php else : ?>
                        <fieldset>
                            <?php foreach ($all_students as $student) : ?>
                                <label style="display: block; margin-bottom: 8px;">
                                    <input type="checkbox" name="assigned_students[]" 
                                           value="<?php echo esc_attr($student->ID); ?>"
                                           <?php checked(in_array($student->ID, $assigned_students)); ?>>
                                    <strong><?php echo esc_html($student->display_name); ?></strong> 
                                    <span style="color: #666;">(<?php echo esc_html($student->user_email); ?>)</span>
                                </label>
                            <?php endforeach; ?>
                        </fieldset>
                        <p class="description">Select which students this parent can manage. The parent can control what content these students can access via the "My Students" menu.</p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
        <?php
    }
    
    // For Student users
    if (in_array('student', $user->roles)) {
        $parent_id = get_user_meta($user->ID, 'assigned_parent', true);
        $parents = get_users(['role' => 'parent']);
        
        // Get current access settings
        $allowed_categories = get_user_meta($user->ID, 'allowed_categories', true);
        $allowed_categories = $allowed_categories ? $allowed_categories : [];
        
        $allowed_tags = get_user_meta($user->ID, 'allowed_tags', true);
        $allowed_tags = $allowed_tags ? $allowed_tags : [];
        
        $can_comment = get_user_meta($user->ID, 'can_comment', true);
        
        ?>
        <h2>Student Settings</h2>
        <table class="form-table" role="presentation">
            <tr>
                <th><label for="assigned_parent">Assigned Parent</label></th>
                <td>
                    <select name="assigned_parent" id="assigned_parent">
                        <option value="">None</option>
                        <?php foreach ($parents as $parent) : ?>
                            <option value="<?php echo esc_attr($parent->ID); ?>" <?php selected($parent_id, $parent->ID); ?>>
                                <?php echo esc_html($parent->display_name); ?> (<?php echo esc_html($parent->user_email); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description">The parent assigned to this student can manage their content access.</p>
                </td>
            </tr>
            <tr>
                <th><label>Current Access Summary</label></th>
                <td>
                    <div style="background: #f0f0f1; padding: 15px; border-left: 4px solid #72aee6;">
                        <p style="margin-top: 0;"><strong>Allowed Categories:</strong> 
                            <?php 
                            if (empty($allowed_categories)) {
                                echo '<span style="color: #d63638;">None - No access to content</span>';
                            } else {
                                $cat_names = [];
                                foreach ($allowed_categories as $cat_id) {
                                    $cat = get_category($cat_id);
                                    if ($cat) {
                                        $cat_names[] = $cat->name;
                                    }
                                }
                                echo '<span style="color: #00a32a;">' . esc_html(implode(', ', $cat_names)) . '</span>';
                            }
                            ?>
                        </p>
                        <p><strong>Allowed Tags:</strong> 
                            <?php 
                            if (empty($allowed_tags)) {
                                echo '<em>None</em>';
                            } else {
                                $tag_names = [];
                                foreach ($allowed_tags as $tag_id) {
                                    $tag = get_tag($tag_id);
                                    if ($tag) {
                                        $tag_names[] = $tag->name;
                                    }
                                }
                                echo '<span style="color: #00a32a;">' . esc_html(implode(', ', $tag_names)) . '</span>';
                            }
                            ?>
                        </p>
                        <p style="margin-bottom: 0;"><strong>Comment Permission:</strong> 
                            <?php 
                            if ($can_comment == '1') {
                                echo '<span style="color: #00a32a;">✓ Enabled</span>';
                            } else {
                                echo '<span style="color: #d63638;">✗ Disabled</span>';
                            }
                            ?>
                        </p>
                    </div>
                    <p class="description">Content access is managed by the assigned parent via the "My Students" menu in the WordPress admin.</p>
                </td>
            </tr>
        </table>
        <?php
    }
}
add_action('show_user_profile', 'spa_add_parent_student_relationship_fields');
add_action('edit_user_profile', 'spa_add_parent_student_relationship_fields');

/**
 * Save parent-student relationship
 */
function spa_save_parent_student_relationship($user_id) {
    if (!current_user_can('manage_options')) {
        return false;
    }
    
    $user = get_userdata($user_id);
    
    // Save for Parent users
    if (in_array('parent', $user->roles)) {
        $old_students = get_user_meta($user_id, 'assigned_students', true);
        $old_students = $old_students ? $old_students : [];
        
        if (isset($_POST['assigned_students'])) {
            $new_students = array_map('intval', $_POST['assigned_students']);
            update_user_meta($user_id, 'assigned_students', $new_students);
            
            // Update reverse relationship for new students
            foreach ($new_students as $student_id) {
                update_user_meta($student_id, 'assigned_parent', $user_id);
            }
            
            // Remove relationship for students no longer assigned
            $removed_students = array_diff($old_students, $new_students);
            foreach ($removed_students as $student_id) {
                $student_parent = get_user_meta($student_id, 'assigned_parent', true);
                if ($student_parent == $user_id) {
                    delete_user_meta($student_id, 'assigned_parent');
                }
            }
        } else {
            // No students selected - remove all relationships
            delete_user_meta($user_id, 'assigned_students');
            foreach ($old_students as $student_id) {
                $student_parent = get_user_meta($student_id, 'assigned_parent', true);
                if ($student_parent == $user_id) {
                    delete_user_meta($student_id, 'assigned_parent');
                }
            }
        }
    }
    
    // Save for Student users
    if (in_array('student', $user->roles)) {
        $old_parent = get_user_meta($user_id, 'assigned_parent', true);
        
        if (isset($_POST['assigned_parent']) && !empty($_POST['assigned_parent'])) {
            $new_parent = intval($_POST['assigned_parent']);
            update_user_meta($user_id, 'assigned_parent', $new_parent);
            
            // Add to new parent's student list
            $parent_students = get_user_meta($new_parent, 'assigned_students', true);
            $parent_students = $parent_students ? $parent_students : [];
            if (!in_array($user_id, $parent_students)) {
                $parent_students[] = $user_id;
                update_user_meta($new_parent, 'assigned_students', $parent_students);
            }
            
            // Remove from old parent's list if changed
            if ($old_parent && $old_parent != $new_parent) {
                $old_parent_students = get_user_meta($old_parent, 'assigned_students', true);
                $old_parent_students = $old_parent_students ? $old_parent_students : [];
                $key = array_search($user_id, $old_parent_students);
                if ($key !== false) {
                    unset($old_parent_students[$key]);
                    update_user_meta($old_parent, 'assigned_students', array_values($old_parent_students));
                }
            }
        } else {
            // Remove parent assignment
            delete_user_meta($user_id, 'assigned_parent');
            
            // Remove from old parent's list
            if ($old_parent) {
                $parent_students = get_user_meta($old_parent, 'assigned_students', true);
                $parent_students = $parent_students ? $parent_students : [];
                $key = array_search($user_id, $parent_students);
                if ($key !== false) {
                    unset($parent_students[$key]);
                    update_user_meta($old_parent, 'assigned_students', array_values($parent_students));
                }
            }
        }
    }
}
add_action('personal_options_update', 'spa_save_parent_student_relationship');
add_action('edit_user_profile_update', 'spa_save_parent_student_relationship');


/**
 * ========================================
 * DASHBOARD WIDGETS
 * ========================================
 */

/**
 * Add dashboard widget for students showing their access
 */
function spa_student_access_dashboard_widget() {
    wp_add_dashboard_widget(
        'student_access_widget',
        'My Content Access',
        'spa_display_student_access_widget'
    );
}

function spa_display_student_access_widget() {
    $current_user = wp_get_current_user();
    
    if (!in_array('student', $current_user->roles)) {
        return;
    }
    
    $allowed_categories = get_user_meta($current_user->ID, 'allowed_categories', true);
    $allowed_categories = $allowed_categories ? $allowed_categories : [];
    
    $allowed_tags = get_user_meta($current_user->ID, 'allowed_tags', true);
    $allowed_tags = $allowed_tags ? $allowed_tags : [];
    
    $can_comment = get_user_meta($current_user->ID, 'can_comment', true);
    $parent_id = get_user_meta($current_user->ID, 'assigned_parent', true);
    
    ?>
    <div class="student-access-info">
        <?php if ($parent_id) : 
            $parent = get_userdata($parent_id);
        ?>
            <p><strong>Your Parent/Guardian:</strong> <?php echo esc_html($parent->display_name); ?></p>
            <hr style="margin: 15px 0;">
        <?php endif; ?>
        
        <?php if (empty($allowed_categories) && empty($allowed_tags)) : ?>
            <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; margin-bottom: 15px;">
                <p style="margin: 0;"><strong>⚠️ No content access assigned yet.</strong><br>
                Your parent/guardian will assign content for you to view.</p>
            </div>
        <?php else : ?>
            
            <p><strong>📚 You can view content from these categories:</strong></p>
            <?php if (empty($allowed_categories)) : ?>
                <p style="margin-left: 20px;"><em>No categories assigned.</em></p>
            <?php else : ?>
                <ul style="margin: 10px 0 15px 20px;">
                    <?php foreach ($allowed_categories as $cat_id) : 
                        $cat = get_category($cat_id);
                        if ($cat) :
                    ?>
                        <li><?php echo esc_html($cat->name); ?> 
                            <span style="color: #666;">(<?php echo $cat->count; ?> posts)</span>
                        </li>
                    <?php 
                        endif;
                    endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <?php if (!empty($allowed_tags)) : ?>
                <p><strong>🏷️ You can also view content with these tags:</strong></p>
                <ul style="margin: 10px 0 15px 20px;">
                    <?php foreach ($allowed_tags as $tag_id) : 
                        $tag = get_tag($tag_id);
                        if ($tag) :
                    ?>
                        <li><?php echo esc_html($tag->name); ?> 
                            <span style="color: #666;">(<?php echo $tag->count; ?> posts)</span>
                        </li>
                    <?php 
                        endif;
                    endforeach; ?>
                </ul>
            <?php endif; ?>
            
            <hr style="margin: 15px 0;">
            
            <p><strong>💬 Comment Permission:</strong> 
                <?php if ($can_comment == '1') : ?>
                    <span style="color: #00a32a;">✓ You can comment on posts</span>
                <?php else : ?>
                    <span style="color: #d63638;">✗ Comments are disabled</span>
                <?php endif; ?>
            </p>
            
        <?php endif; ?>
        
        <p style="margin-top: 15px;">
            <a href="<?php echo home_url(); ?>" class="button button-primary">View Available Content</a>
        </p>
    </div>
    <?php
}

function spa_add_student_dashboard_widget() {
    $current_user = wp_get_current_user();
    if (in_array('student', $current_user->roles)) {
        add_action('wp_dashboard_setup', 'spa_student_access_dashboard_widget');
    }
}
add_action('admin_init', 'spa_add_student_dashboard_widget');


/**
 * ========================================
 * ADMIN NOTICES
 * ========================================
 */

/**
 * Show admin notice for parents without students
 */
function spa_parent_no_students_notice() {
    $current_user = wp_get_current_user();
    
    if (!in_array('parent', $current_user->roles)) {
        return;
    }
    
    $assigned_students = get_user_meta($current_user->ID, 'assigned_students', true);
    
    if (empty($assigned_students)) {
        ?>
        <div class="notice notice-warning">
            <p><strong>Welcome, Parent!</strong> You don't have any students assigned yet. Please contact an administrator to assign students to your account so you can manage their content access.</p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'spa_parent_no_students_notice');