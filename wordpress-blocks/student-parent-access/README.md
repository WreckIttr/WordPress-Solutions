# Student Parent Access Control - WordPress Plugin

A WordPress plugin that creates a student-parent role system with category and tag-based content access control. Perfect for educational institutions, tutoring platforms, homeschool networks, and any site requiring parent-managed student content access.

[![WordPress](https://img.shields.io/badge/WordPress-5.0%2B-blue.svg)](https://wordpress.org/)
[![PHP](https://img.shields.io/badge/PHP-7.2%2B-purple.svg)](https://php.net/)
[![License](https://img.shields.io/badge/License-GPL%20v2%2B-green.svg)](https://www.gnu.org/licenses/gpl-2.0.html)

## 📋 Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [User Roles](#user-roles)
- [Screenshots](#screenshots)
- [Frequently Asked Questions](#frequently-asked-questions)
- [Contributing](#contributing)
- [Support](#support)
- [License](#license)
- [Changelog](#changelog)

## ✨ Features

### Core Functionality
- **Custom User Roles**: Adds Student and Parent roles with specific capabilities
- **Category-Based Access**: Control content visibility by WordPress categories
- **Tag-Based Access**: Additional content filtering using WordPress tags
- **Comment Control**: Parents can enable/disable commenting per student
- **Scalable Content Management**: Automatically applies to new posts in assigned categories/tags
- **Parent Dashboard**: Dedicated interface for managing multiple students
- **Student Dashboard Widget**: Shows students their current access permissions
- **Automatic Content Filtering**: Students only see authorized content throughout the site
- **Security**: Prevents unauthorized access to restricted posts

### Parent Capabilities
- Manage multiple students from a single account
- Assign categories and tags to each student independently
- Control commenting permissions per student
- All Author role capabilities (create, edit, publish posts)
- View student access summary

### Student Experience
- Subscribe-level base permissions
- View only content from assigned categories/tags
- Comment on allowed posts (if enabled by parent)
- Dashboard widget showing access permissions
- Clean, filtered content experience

## 🔧 Requirements

- **WordPress**: 5.0 or higher
- **PHP**: 7.2 or higher
- **MySQL**: 5.6 or higher / MariaDB 10.0 or higher

## 📥 Installation

### Method 1: Manual Installation

1. Download the plugin ZIP file or clone this repository:
```bash
   git clone https://github.com/wreckittr/student-parent-access.git
```

2. Upload the `student-parent-access` folder to `/wp-content/plugins/` directory

3. Activate the plugin through the 'Plugins' menu in WordPress

4. The Student and Parent roles are created automatically upon activation

### Method 2: WordPress Admin Upload

1. Download the ZIP file from [releases](https://github.com/wreckittr/student-parent-access/releases)

2. Go to **Plugins** → **Add New** → **Upload Plugin**

3. Choose the ZIP file and click **Install Now**

4. Activate the plugin

## 🚀 Usage

### Initial Setup (Administrator)

1. **Create Student Accounts**
   - Go to **Users** → **Add New**
   - Create user with **Student** role
   - Repeat for all students

2. **Create Parent Accounts**
   - Go to **Users** → **Add New**
   - Create user with **Parent** role
   - Repeat for all parents

3. **Assign Students to Parents**
   - Go to **Users** → **All Users**
   - Edit a Parent user
   - Scroll to "Parent Settings" section
   - Check the students assigned to this parent
   - Click **Update User**

### Parent Workflow

1. **Log in** with Parent account

2. **Navigate to "My Students"** menu in WordPress admin

3. **Configure each student's access:**
   - Select categories the student can view (hold Ctrl/Cmd for multiple)
   - Select tags the student can view (hold Ctrl/Cmd for multiple)
   - Enable/disable commenting permission
   - Click **Save All Settings**

4. **Create content** using standard WordPress post editor
   - Assign appropriate categories/tags
   - Students with matching access will automatically see new posts

### Student Experience

1. **Log in** with Student account

2. **View Dashboard** to see assigned content access

3. **Browse site** - only posts from assigned categories/tags are visible

4. **Comment** (if enabled by parent)

## 👥 User Roles

### Student Role
**Base Capabilities:** Same as Subscriber
- `read` - View the WordPress admin dashboard
- Can only view content from assigned categories/tags
- Comment permission controlled by parent
- Cannot create, edit, or delete posts

### Parent Role
**Base Capabilities:** All Author capabilities + Custom
- `edit_posts` - Edit own posts
- `publish_posts` - Publish own posts
- `delete_posts` - Delete own posts
- `upload_files` - Upload media
- `manage_students` - Access student management dashboard
- `assign_content_to_students` - Control student content access
- `view_student_activity` - View student information

### Administrator
- Full control over all plugin features
- Assign students to parents
- Override any content restrictions
- Manage all user accounts

## 📸 Screenshots

### Parent Management Dashboard
![Parent Dashboard](screenshots/parent-dashboard.png)
*Parents can manage content access for multiple students from one interface*

### Student Dashboard Widget
![Student Widget](screenshots/student-widget.png)
*Students see their current access permissions and assigned parent*

### User Profile Integration
![User Profile](screenshots/user-profile.png)
*Administrators assign students to parents in user profiles*

### Content Filtering
![Content Filter](screenshots/content-filtering.png)
*Students only see posts from their assigned categories and tags*

## ❓ Frequently Asked Questions

### How does the content filtering work?

Students can view posts that match **ANY** of their assigned categories **OR** tags. This uses "OR" logic, so a post only needs one matching category or tag for the student to see it.

### Can a student have multiple parents?

No, each student can only be assigned to one parent account. However, a parent can manage multiple students.

### What happens if no categories or tags are assigned to a student?

The student will not be able to view any content on the site. They'll see an empty posts list.

### Can parents see all content on the site?

Yes, parents have Author-level capabilities and can view all published content, create their own posts, and manage their assigned students' access.

### Does this work with custom post types?

Currently, the plugin only filters the standard WordPress "post" post type. Custom post type support may be added in future versions.

### Can students access posts through direct URLs?

No, if a student tries to access a post they're not authorized to view (even via direct URL), they will be redirected to the home page.

### What happens to student access when I delete a category?

If a category is deleted, it's automatically removed from students' allowed categories. Students will lose access to posts that were only in that category.

### Can I export student access settings?

The settings are stored in WordPress user meta. You can use standard WordPress user export/import tools, but this feature may be enhanced in future versions.

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Development Setup

1. Clone the repository:
```bash
   git clone https://github.com/WreckIttr/WordPress-Solutions/student-parent-access.git
```

2. Install on a local WordPress installation

3. Make your changes

4. Test thoroughly with both Student and Parent accounts

5. Submit a pull request with a clear description of changes

### Coding Standards

- Follow [WordPress Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/)
- Prefix all functions with `spa_`
- Add appropriate inline documentation
- Test with WordPress Debug mode enabled

## 💬 Support

- **Issues**: [GitHub Issues](https://github.com/wreckittr/WordPress-Solutions/student-parent-access/issues)
- **Documentation**: [Wiki](https://github.com/wreckittr/WordPress-Solutions/student-parent-access/wiki)
- **Questions**: [Discussions](https://github.com/wreckittr/WordPress-Solutions/student-parent-access/discussions)

## 📄 License

This plugin is licensed under the [GNU General Public License v2.0 or later](https://www.gnu.org/licenses/gpl-2.0.html).

## 🔄 Changelog

### Version 1.0.0 - 2024-01-15
**Initial Release**
- Student and Parent role creation
- Category-based content filtering
- Tag-based content filtering
- Parent management dashboard
- Student dashboard widget
- Comment control per student
- User profile integration
- Security and access controls

## 🗺️ Roadmap

Future enhancements being considered:

- [ ] Custom post type support
- [ ] Student activity logging
- [ ] Email notifications for parents
- [ ] Bulk student import/export
- [ ] Access scheduling (time-based access)
- [ ] Content completion tracking
- [ ] Student progress reports
- [ ] Multi-parent support per student
- [ ] REST API endpoints
- [ ] Mobile app integration

## 👨‍💻 Author

**Your Name**
- GitHub: [@wreckittr](https://github.com/wreckittr)
- Website: [https://wreckitsolutions.blog](https://wreckitsolutions.blog)

## 🙏 Acknowledgments

- Built for WordPress community
- Inspired by educational institution needs
- Thanks to all contributors

---

**⭐ If you find this plugin useful, please consider starring the repository!**

**🐛 Found a bug?** [Report it here](https://github.com/wreckittr/WordPress-Solutions/student-parent-access/issues)

**💡 Have a feature request?** [Share your ideas](https://github.com/wreckittr/WordPress-Solutions/student-parent-access/discussions)