# WordPress Taxonomy Nested-Set

### What is the problem?

If you see the WordPress database structure you see there are 3 tables to store
terms and taxonomies and to get hierarchy of terms or get children or
ancestors of these terms we should join terms multiple times and this is very slow when
you have thousands of posts or products and hundreds or thousands of terms.

### What does this plugin do?

In this plugin we have added a table named `{WP_PREFIX}_taxonomy_lookup`
and store all the terms with [nested-set](https://en.wikipedia.org/wiki/Nested_set_model) algorithm

With this structure, we can get the hierarchy of terms with just one query and save a lot of time and memory.

## Getting started

Download or clone this repository and copy it in `wordpress/wp-content/plugins`
directory and then activate it in admin panel.

In admin panel a page named `nested-term` have been added.

To create table and insert terms in lookup table click on `Begin Install` button.
It may take several minutes based on numbers of terms you have.

There is another button `Re-Generate` it is when table values
has been changed manually and want to fix this. It will re-insert terms with parents and fix tree.

### Hooks

Action has been added to terms and when a term gets created, updated or deleted
it will handle it in lookup table.

### Get terms

to get terms you should create an instance of `Nested_Term_Query` and then
call `get_terms( $args )` and the $args are same
with [get-terms](https://developer.wordpress.org/reference/functions/get_terms/).

### Functions

There are some helpers functions to work with terms.

| Function Name                                             | Description                                  | Output        |
|-----------------------------------------------------------|----------------------------------------------|---------------|
| `nested_get_term(string $term_id, string $taxonomy = "")` | Get the term with given term_id and taxonomy | `Nested_Term` |
| `nested_update_term( int $term_id, array $args )`         | Update the term                              | `int, false`  |
| `nested_delete_term( int $term_id ) `                     | Delete the term                              | `Int, False`  |
| `nested_get_ancestors( int $term_id )`                    | Get array of term's ancestors                | `Array`       |


# Contribution
Please send pull requests improving the usage and fixing bugs, improving documentation and providing better examples.
