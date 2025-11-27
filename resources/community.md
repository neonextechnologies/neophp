# Community & Support

Connect with the NeoPHP community and get help when you need it.

## Table of Contents

- [Official Channels](#official-channels)
- [Getting Help](#getting-help)
- [Community Guidelines](#community-guidelines)
- [Events & Meetups](#events--meetups)
- [Contributing](#contributing)
- [Recognition](#recognition)

---

## Official Channels

### GitHub

**Repository:** https://github.com/neonextechnologies/neophp

- Report bugs and issues
- Request features
- Submit pull requests
- Browse source code

**Discussions:** https://github.com/neonextechnologies/neophp/discussions

- Ask questions
- Share projects
- Discuss ideas
- Get community feedback

### Discord

**Server:** https://discord.gg/neophp

Active channels:

- `#general` - General discussion
- `#help` - Get technical help
- `#showcase` - Share your projects
- `#announcements` - Official announcements
- `#contributions` - Discuss contributions
- `#off-topic` - Non-technical chat

### Twitter

**Follow:** [@NeoPHP](https://twitter.com/neophp)

- Framework updates
- Tips and tricks
- Community highlights
- Event announcements

### Newsletter

**Subscribe:** https://neophp.dev/newsletter

Monthly newsletter featuring:

- New releases
- Featured tutorials
- Community projects
- Upcoming events

### YouTube

**Channel:** [NeoPHP Official](https://youtube.com/@neophp)

Content:

- Tutorial videos
- Live coding sessions
- Conference talks
- Feature demonstrations

---

## Getting Help

### Before Asking

1. **Search documentation** - Check if answer exists in docs
2. **Search GitHub issues** - Someone may have asked already
3. **Search Discord** - Question might have been answered
4. **Try debugging** - Use error messages and stack traces

### How to Ask Good Questions

**Include:**

- NeoPHP version
- PHP version
- Operating system
- What you're trying to achieve
- What you've tried
- Error messages/stack traces
- Relevant code snippets

**Example Good Question:**

```markdown
## Issue with Query Builder Join

**NeoPHP Version:** 1.0.0
**PHP Version:** 8.1.2
**OS:** Ubuntu 22.04

I'm trying to join two tables with a custom condition but getting a SQL error.

**What I'm trying:**
```php
$posts = DB::table('posts')
    ->join('users', function($join) {
        $join->on('posts.user_id', '=', 'users.id')
             ->where('users.active', '=', 1);
    })
    ->get();
```

**Error:**
```
SQLSTATE[42000]: Syntax error near 'WHERE'
```

**What I've tried:**
- Moving the where clause outside the join
- Using different join types

Any guidance would be appreciated!
```

### Where to Ask

**GitHub Discussions** - Best for:

- General questions
- Best practices
- Architecture decisions
- "How do I...?" questions

**Discord #help** - Best for:

- Quick questions
- Real-time help
- Debugging sessions
- Clarifications

**GitHub Issues** - Only for:

- Confirmed bugs
- Feature requests
- Documentation issues

**Stack Overflow** - Tag: `neophp`

- Programming questions
- Troubleshooting
- Code reviews

---

## Community Guidelines

### Be Respectful

- Treat everyone with respect
- Be patient with beginners
- No harassment or discrimination
- Keep discussions professional

### Be Helpful

- Answer questions when you can
- Share your knowledge
- Provide constructive feedback
- Credit others' contributions

### Be Collaborative

- Discuss, don't argue
- Consider different viewpoints
- Focus on solutions
- Help improve the community

### Don't

- Spam or self-promote excessively
- Share private/sensitive information
- Post off-topic content
- Ask for homework solutions

---

## Events & Meetups

### NeoPHP Conf

Annual conference featuring:

- Keynote presentations
- Technical workshops
- Lightning talks
- Networking sessions

**Next Event:** TBA  
**Location:** Virtual + Select cities  
**Registration:** https://conf.neophp.dev

### Local Meetups

Find or organize local meetups:

**Find Meetups:** https://meetups.neophp.dev

**Organize a Meetup:**

1. Join Discord and announce your interest
2. Get official meetup kit
3. Find a venue
4. Schedule and promote event
5. Report back to community

**Support Available:**

- Promotional materials
- Swag for attendees
- Connection to speakers
- Social media promotion

### Online Events

**Monthly Office Hours**

- First Tuesday of every month
- 2 PM UTC
- Live Q&A with core team
- Join: https://meet.neophp.dev

**Weekly Code Reviews**

- Every Friday
- Review community code
- Learn best practices
- Get feedback on your code

**Quarterly Webinars**

- Deep dives into features
- Guest speakers
- Live demos
- Interactive Q&A

---

## Contributing

### Ways to Contribute

**Code:**

- Fix bugs
- Add features
- Improve performance
- Write tests

**Documentation:**

- Fix typos
- Add examples
- Write tutorials
- Translate docs

**Community:**

- Answer questions
- Review pull requests
- Organize meetups
- Create content

**Support:**

- Sponsor development
- Provide infrastructure
- Offer services
- Donate

### Getting Started

1. Read [Contributing Guidelines](../contributing/guidelines.md)
2. Find an issue tagged `good-first-issue`
3. Comment that you're working on it
4. Submit your pull request

### Maintainer Program

Become a maintainer:

**Requirements:**

- Regular contributions (6+ months)
- Good understanding of codebase
- Active community participation
- Code review experience

**Benefits:**

- Commit access
- Decision-making authority
- Recognition in community
- Exclusive swag

**Apply:** Email maintainers@neophp.dev

---

## Recognition

### Hall of Fame

Top contributors featured on:

- Official website
- GitHub README
- Conference mentions
- Newsletter spotlights

### Badges & Achievements

Earn badges for:

- First contribution
- 10 merged PRs
- 50 merged PRs
- 100+ merged PRs
- Helping in community
- Organizing events

### Annual Awards

**NeoPHP Community Awards**

Categories:

- Contributor of the Year
- Most Helpful Community Member
- Best Tutorial/Content
- Most Innovative Project
- Rising Star

**Nominated by:** Community vote  
**Announced at:** NeoPHP Conf

---

## User Groups

### Official User Groups

**NeoPHP Developers**

- Discord: #developers
- Focus: Building with NeoPHP
- Weekly discussions

**NeoPHP Contributors**

- Discord: #contributors
- Focus: Framework development
- Code reviews and planning

**NeoPHP Educators**

- Discord: #educators
- Focus: Teaching and content creation
- Share resources

### Regional Groups

**North America:**

- US East: discord.gg/neophp-us-east
- US West: discord.gg/neophp-us-west
- Canada: discord.gg/neophp-canada

**Europe:**

- UK: discord.gg/neophp-uk
- Germany: discord.gg/neophp-de
- France: discord.gg/neophp-fr

**Asia Pacific:**

- Japan: discord.gg/neophp-jp
- India: discord.gg/neophp-in
- Australia: discord.gg/neophp-au

**Start Your Own:**

Contact community@neophp.dev to create a regional group.

---

## Partners & Sponsors

### Framework Sponsors

Support NeoPHP development:

**Gold Sponsors:**

- [Sponsor Name] - Description
- [Sponsor Name] - Description

**Silver Sponsors:**

- [Sponsor Name] - Description
- [Sponsor Name] - Description

**Become a Sponsor:**

https://github.com/sponsors/neophp

### Community Partners

Organizations supporting the ecosystem:

- **Hosting Partners** - Provide infrastructure
- **Education Partners** - Offer training
- **Service Partners** - Provide services

**Become a Partner:**

Email partnerships@neophp.dev

---

## Code of Conduct

Full Code of Conduct: [CODE_OF_CONDUCT.md](../CODE_OF_CONDUCT.md)

### Reporting Issues

If you experience or witness unacceptable behavior:

1. **Report to:** conduct@neophp.dev
2. **Include:** Details of incident, people involved, any evidence
3. **Expect:** Response within 48 hours

All reports are confidential and taken seriously.

---

## Resources

### Official Resources

- **Website:** https://neophp.dev
- **Documentation:** https://docs.neophp.dev
- **Blog:** https://blog.neophp.dev
- **GitHub:** https://github.com/neonextechnologies/neophp

### Social Media

- **Twitter:** @neophp
- **LinkedIn:** linkedin.com/company/neophp
- **Reddit:** r/neophp
- **Dev.to:** dev.to/neophp

### Contact

- **General:** hello@neophp.dev
- **Security:** security@neophp.dev
- **Press:** press@neophp.dev
- **Partnerships:** partnerships@neophp.dev

---

## Stay Connected

Join thousands of developers in the NeoPHP community:

1. ⭐ Star the [GitHub repo](https://github.com/neonextechnologies/neophp)
2. 💬 Join [Discord](https://discord.gg/neophp)
3. 🐦 Follow on [Twitter](https://twitter.com/neophp)
4. 📧 Subscribe to [Newsletter](https://neophp.dev/newsletter)
5. 🎥 Subscribe on [YouTube](https://youtube.com/@neophp)

**We're stronger together!** 🚀
