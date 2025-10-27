# Tests Contribution Notes

- When rendering Livewire components like `CommentManager` outside the Livewire runtime (for example in HTTP or Filament tests), be sure to pass the stateful properties (`replyingToId`, `editingCommentId`, `editingContent`, `content`) and a fresh `ViewErrorBag` into the Blade view so templates expecting those variables continue to work.
- Continue adding descriptive comments inside every new test to satisfy the repository-wide guidance about documented code.
