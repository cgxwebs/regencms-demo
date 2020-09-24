RegenCMS Demo
=============

RegenCMS is a content management system that is capable of aggregating content across multiple frontend.
It is intended to accommodate flexible moderation worfklows, and content organization using tags, and role-based users.
This app is for exploratory purposes. Built with Laravel 7.

Features
---------
- Tags up to 2-levels that can be used for content organization, workflows and extended permission system
- Tags have visibility levels from hidden, unlisted to visible
- Channels are primarily to differentiate domains or hostnames
- Channels can also be used for creating multiple environments and staging grounds
- Role-based user system with channel access
- Stories are the main content nodes and can parse HTML, Markdown/Commonmark, Plaintext or JSON
- Each story can have multiple content blocks with their own format type
- Media manager for file uploads, wherein a lower role cannot modify uploads by higher roles
- API for consuming visible stories across a channel and its varying tag hierarchies

**Disclaimer**

This repository is a subset of the original source code and should not be used in production.
