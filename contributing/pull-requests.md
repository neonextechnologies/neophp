# Pull Request Guide

This guide walks you through creating high-quality pull requests for NeoPHP.

## Table of Contents

- [Before You Start](#before-you-start)
- [Creating Your Branch](#creating-your-branch)
- [Making Changes](#making-changes)
- [Writing Commits](#writing-commits)
- [Preparing Your PR](#preparing-your-pr)
- [Submitting the PR](#submitting-the-pr)
- [Review Process](#review-process)
- [After Merge](#after-merge)

---

## Before You Start

### Check Existing Work

1. **Search existing PRs** to avoid duplicate work
2. **Check open issues** for related discussions
3. **Review project roadmap** to ensure alignment

### Discuss Major Changes

For significant features or changes:

1. **Open an issue** to discuss the approach
2. **Wait for maintainer feedback** before implementing
3. **Get consensus** on the solution

### Update Your Fork

```bash
# Add upstream if not already added
git remote add upstream https://github.com/neonextechnologies/neophp.git

# Fetch latest changes
git fetch upstream

# Update your develop branch
git checkout develop
git merge upstream/develop
git push origin develop
```

---

## Creating Your Branch

### Branch Naming

Use descriptive names with prefixes:

- `feature/` - New features
- `bugfix/` - Bug fixes
- `hotfix/` - Urgent production fixes
- `refactor/` - Code refactoring
- `docs/` - Documentation changes

**Examples:**

```bash
feature/add-redis-cluster-support
bugfix/fix-query-builder-null-handling
hotfix/security-validation-bypass
refactor/optimize-database-connection-pool
docs/update-installation-guide
```

### Create Branch

```bash
# Create and switch to new branch
git checkout -b feature/your-feature-name

# Or using git switch (Git 2.23+)
git switch -c feature/your-feature-name
```

---

## Making Changes

### Keep Changes Focused

- **One concern per PR** - separate features/fixes into different PRs
- **Atomic commits** - each commit should be a logical unit
- **No unrelated changes** - avoid fixing unrelated issues in the same PR

### Write Tests

Every PR should include appropriate tests:

**For Bug Fixes:**

```php
<?php

namespace Tests\Unit\Database;

use Neo\Database\QueryBuilder;
use PHPUnit\Framework\TestCase;

class QueryBuilderTest extends TestCase
{
    /**
     * Test that null values are properly handled in where clauses
     * 
     * @test
     * @see https://github.com/neonextechnologies/neophp/issues/123
     */
    public function it_handles_null_values_in_where_clauses()
    {
        $builder = new QueryBuilder();
        
        $query = $builder->table('users')
            ->where('deleted_at', null)
            ->toSql();
        
        $this->assertEquals(
            'SELECT * FROM users WHERE deleted_at IS NULL',
            $query
        );
    }
}
```

**For New Features:**

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Neo\Cache\RedisCluster;

class RedisClusterTest extends TestCase
{
    /**
     * Test Redis Cluster basic operations
     * 
     * @test
     */
    public function it_can_store_and_retrieve_from_cluster()
    {
        $cluster = new RedisCluster($this->getClusterConfig());
        
        $cluster->put('test-key', 'test-value', 60);
        
        $this->assertEquals('test-value', $cluster->get('test-key'));
    }
    
    /**
     * Test failover handling
     * 
     * @test
     */
    public function it_handles_node_failover()
    {
        $cluster = new RedisCluster($this->getClusterConfig());
        
        // Store data
        $cluster->put('test-key', 'test-value', 60);
        
        // Simulate node failure
        $this->simulateNodeFailure();
        
        // Should still retrieve data from other nodes
        $this->assertEquals('test-value', $cluster->get('test-key'));
    }
}
```

### Update Documentation

If your changes affect:

- **Public API** - Update API reference
- **Configuration** - Update configuration docs
- **Usage** - Add examples to tutorials
- **Breaking changes** - Update upgrade guide

### Follow Code Style

Run style checks:

```bash
# Check code style
composer cs-check

# Fix code style
composer cs-fix
```

---

## Writing Commits

### Commit Message Format

Follow [Conventional Commits](https://www.conventionalcommits.org/):

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

- `feat` - New feature
- `fix` - Bug fix
- `docs` - Documentation
- `style` - Formatting
- `refactor` - Code restructuring
- `perf` - Performance improvement
- `test` - Adding tests
- `chore` - Maintenance

### Scopes

Common scopes:

- `database` - Database layer
- `query-builder` - Query builder
- `cache` - Caching system
- `queue` - Queue system
- `validation` - Validation
- `http` - HTTP layer
- `routing` - Routing
- `auth` - Authentication

### Examples

**Simple commit:**

```bash
git commit -m "feat(cache): add Redis Cluster support"
```

**Detailed commit:**

```bash
git commit -m "feat(cache): add Redis Cluster support

Add support for Redis Cluster mode in the caching system. This enables
high-availability configurations with automatic failover.

Changes:
- Add RedisCluster driver
- Implement cluster configuration
- Add failover handling
- Update documentation

Closes #123"
```

**Breaking change:**

```bash
git commit -m "feat(database)!: change connection configuration format

BREAKING CHANGE: Database connection configuration format has changed.
Update your config/database.php to use the new format:

Before:
'mysql' => ['host' => 'localhost']

After:
'mysql' => [
    'driver' => 'mysql',
    'host' => 'localhost'
]

See upgrade guide for migration instructions."
```

### Commit Best Practices

**Keep commits atomic:**

```bash
# Good - separate logical changes
git add src/Cache/RedisCluster.php
git commit -m "feat(cache): add RedisCluster driver"

git add tests/Unit/Cache/RedisClusterTest.php
git commit -m "test(cache): add RedisCluster tests"

git add docs/cache.md
git commit -m "docs(cache): document Redis Cluster usage"

# Bad - mixing unrelated changes
git add src/Cache/RedisCluster.php src/Database/Connection.php docs/
git commit -m "add Redis Cluster and fix database connection and update docs"
```

**Write meaningful messages:**

```bash
# Good
git commit -m "fix(validation): resolve email validation regex issue

The regex pattern was incorrectly rejecting valid email addresses
containing plus signs. Updated pattern to comply with RFC 5322.

Fixes #456"

# Bad
git commit -m "fix bug"
git commit -m "update code"
git commit -m "changes"
```

---

## Preparing Your PR

### Pre-submission Checklist

Before submitting, ensure:

```bash
# 1. All tests pass
composer test

# 2. Code style is correct
composer cs-check

# 3. Static analysis passes
composer phpstan

# 4. No merge conflicts
git fetch upstream
git rebase upstream/develop

# 5. Commits are clean
git log --oneline
```

### Squash if Needed

If you have many small commits, consider squashing:

```bash
# Interactive rebase last 5 commits
git rebase -i HEAD~5

# In editor, mark commits to squash:
pick abc1234 feat(cache): add Redis Cluster
squash def5678 fix typo
squash ghi9012 fix tests
squash jkl3456 update docs
```

### Update Your Branch

```bash
# Rebase on latest develop
git fetch upstream
git rebase upstream/develop

# Resolve conflicts if any
git add .
git rebase --continue

# Force push (only on your branch!)
git push origin feature/your-feature-name --force-with-lease
```

---

## Submitting the PR

### PR Title

Clear, descriptive titles:

```
feat(cache): add Redis Cluster support
fix(validation): resolve email regex issue with plus signs
docs(installation): add Windows troubleshooting section
refactor(database): optimize connection pool management
```

### PR Description

Use the template:

```markdown
## Description

Brief description of what this PR does and why.

## Type of Change

- [ ] Bug fix (non-breaking change fixing an issue)
- [x] New feature (non-breaking change adding functionality)
- [ ] Breaking change (fix or feature causing existing functionality to change)
- [ ] Documentation update
- [ ] Performance improvement
- [ ] Code refactoring

## Related Issue

Closes #123

## Changes Made

- Add RedisCluster driver class
- Implement cluster configuration parsing
- Add automatic failover handling
- Write comprehensive test suite
- Update cache documentation with cluster examples

## Testing

### Manual Testing

Tested with 3-node Redis Cluster:
1. Basic set/get operations ✓
2. Node failover scenarios ✓
3. Key distribution across nodes ✓

### Automated Testing

All tests pass:
```bash
composer test
# 42 tests, 150 assertions, 0 failures
```

## Performance Impact

Benchmark results show cluster operations within 5% of single-instance:

| Operation | Single | Cluster | Diff |
|-----------|--------|---------|------|
| Get       | 0.5ms  | 0.52ms  | +4%  |
| Set       | 0.6ms  | 0.63ms  | +5%  |

## Breaking Changes

None. This is an additive feature with backward compatibility.

## Screenshots (if applicable)

N/A

## Checklist

- [x] Code follows style guidelines
- [x] Self-review performed
- [x] Comments added for complex code
- [x] Documentation updated
- [x] Tests added covering new code
- [x] All tests passing locally
- [x] No new warnings generated
- [x] Dependent changes merged
- [x] Updated CHANGELOG.md

## Additional Notes

Future improvements could include:
- Connection pool optimizations
- Advanced cluster monitoring
- Cluster rebalancing utilities
```

### Example PRs

**Bug Fix PR:**

```markdown
## Description

Fixes Query Builder issue where `whereNull()` was generating incorrect SQL
when combined with other where clauses.

## Type of Change

- [x] Bug fix (non-breaking change fixing an issue)

## Related Issue

Fixes #456

## Changes Made

- Fix SQL generation in QueryBuilder::whereNull()
- Add proper NULL handling in where clause combinations
- Add regression tests

## Testing

Added 3 new test cases covering:
1. Single whereNull() clause
2. whereNull() combined with where()
3. whereNull() in nested where groups

All tests pass.

## Checklist

- [x] All items checked
```

**Feature PR:**

```markdown
## Description

Adds support for Redis Cluster mode in the caching system, enabling
high-availability configurations with automatic failover.

## Type of Change

- [x] New feature (non-breaking change adding functionality)

## Related Issue

Closes #123

## Changes Made

[Detailed changes as shown above]

## Checklist

- [x] All items checked
```

---

## Review Process

### What to Expect

1. **Automated checks** run first (CI/CD)
2. **Code review** by maintainers (1-3 days)
3. **Feedback** as review comments
4. **Discussion** on implementation approach
5. **Approval** when ready
6. **Merge** by maintainers

### Responding to Feedback

**Be receptive:**

```markdown
> Consider using dependency injection here instead of creating a new instance.

Good response:
"Good catch! I've updated it to use DI. Let me know if this looks better."

Bad response:
"It works fine as is."
```

**Ask for clarification:**

```markdown
"Could you elaborate on the performance concern? I'm happy to optimize
this but want to make sure I understand the issue correctly."
```

**Provide context:**

```markdown
"I chose this approach because [reason]. However, I'm open to alternatives.
What do you think about [alternative approach]?"
```

### Making Updates

```bash
# Make changes based on feedback
# ... edit files ...

# Commit changes
git add .
git commit -m "refactor: use dependency injection for cache driver"

# Push updates
git push origin feature/your-feature-name
```

### Re-request Review

After addressing feedback, re-request review on GitHub.

---

## After Merge

### Cleanup

```bash
# Switch to develop
git checkout develop

# Pull latest (includes your changes!)
git pull upstream develop

# Delete your feature branch
git branch -d feature/your-feature-name
git push origin --delete feature/your-feature-name

# Update your fork
git push origin develop
```

### Update Related Issues

If your PR closes issues:

1. Ensure issues are auto-closed (via "Closes #123" in PR)
2. Add closing comments if needed
3. Update any related documentation issues

### Share the News

- Tweet about your contribution
- Share in relevant communities
- Update your portfolio/resume

---

## Common Issues

### Merge Conflicts

```bash
# Update your branch
git fetch upstream
git rebase upstream/develop

# Resolve conflicts in your editor
# Look for conflict markers: <<<<<<<, =======, >>>>>>>

# After resolving
git add .
git rebase --continue

# Push updates
git push origin feature/your-feature-name --force-with-lease
```

### CI/CD Failures

Common causes:

1. **Tests failing** - Run `composer test` locally
2. **Code style** - Run `composer cs-fix`
3. **Static analysis** - Run `composer phpstan`
4. **Merge conflicts** - Rebase on develop

### Accidentally Pushed to Main

```bash
# Don't panic! Create a new branch from your changes
git checkout -b feature/rescued-changes

# Reset main to upstream
git checkout main
git reset --hard upstream/main
git push origin main --force-with-lease

# Continue work on feature branch
git checkout feature/rescued-changes
```

---

## PR Etiquette

### Do

✅ Keep PRs focused and reasonably sized
✅ Respond to feedback promptly
✅ Be patient during review
✅ Thank reviewers for their time
✅ Help review other PRs

### Don't

❌ Submit giant PRs (split into smaller ones)
❌ Force-push without communication
❌ Take feedback personally
❌ Ping maintainers repeatedly
❌ Submit without testing

---

## Getting Help

If you're stuck:

1. **Comment on your PR** with specific questions
2. **Ask in Discord** for general help
3. **Check documentation** for guidance
4. **Review similar PRs** for examples

---

## Recognition

Quality PRs are recognized through:

- Mention in release notes
- Contributor badge
- Featured in newsletters
- Annual contributor awards

---

## Resources

- [GitHub PR Documentation](https://docs.github.com/en/pull-requests)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [How to Write a Git Commit Message](https://chris.beams.io/posts/git-commit/)

---

Thank you for contributing to NeoPHP! Your PRs make the framework better for everyone. 🙏
