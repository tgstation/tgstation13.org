# Contributing Guidelines

## Issues

### Be mildly civil

Issue reports that start out by rudely throwing blame around are subject to closeure.

### Be clear and concise.

State what the issue is from a "whats wrong" prospective.
Bug reports should clearly allow maintainers to understand whats wrong and how to test/reproduce.

## PRs

### Hide something, get rejected.

Your PR should clearly state what it does. Adding unrelated things then never mentioning them will not fool us.
Breaking this rule too many times will lead to a block.
Breaking this rule to add something that compromises the security of this application will lead to a block and report to github and/or the proper authorities.

## Style and Code Requirements

### Seperate logic and content.

In the bandb stuff: At no point should any php code not in template.php echo html, javascript, or css. Furthermore: no php code should be sending the template class html code as a variable.
Rather, have all content(html,js,css) in template files or included from template files, add template variables for dynamic content, and use defined conditionals in templates to determine when something should show. (see bandetails.php and template/bandetails.tpl for an example)

--to be continued--
