# Students Block

A Gutenberg block plugin to display students with flexible filtering options.

## Features

- Display multiple students or a single student
- Filter by status (Active/Inactive/All)
- Customizable number of students to display
- Responsive grid layout
- Server-side rendering for optimal performance

## Installation

1. Upload the plugin to `/wp-content/plugins/students-block`
2. Activate the plugin through the WordPress admin
3. Install dependencies: `npm install`
4. Build the block: `npm run build`
5. Add the "Students Block" to your pages/posts

## Development

```bash
# Install dependencies
npm install

# Build for production
npm run build

# Start development mode (watch for changes)
npm start
```

## Block Settings

The block includes the following settings in the sidebar:

- **Show Single Student**: Toggle to show only one student
- **Select Student**: Dropdown to choose a specific student (when single mode is enabled)
- **Number of Students**: Range control (1-20) to set how many students to display
- **Filter by Status**: Select to filter by Active, Inactive, or All students

## Requirements

- WordPress 6.0+
- PHP 7.4+
- Students custom post type must be registered

