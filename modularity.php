<?php
include "common.php";
essay_begin($modularity_page);
?>

<p>Sometimes I see people attempt to make programs more modular by
taking a big file and splitting it into a bunch of small files, or by
grouping related stuff into folder and namespaces. I think these things are
distractions from real work and hacks to work around shortcomings in one's
tools or abilities.</p>

<p>Modularity really comes from a lack of dependencies between parts of the
system. By "dependency" I mean not only the library and rule dependencies
explicit in the build system, but also the numerous implicit dependencies
between modules, functions, and even sub-expressions within a function. A
dependency is any assumption that one part of a program makes about another
part.</p>

<p>My view is that improvements to modularity are changes that improve
the dependency graph of the program. It is difficult to actually construct
this graph, since it is unclear what granularity of dependency is
appropriate, but you can at least form a vague picture of some of the nodes
and edges that make up the macro-structure of the part of a system that the
change affects.</p>

<p>I have a few heuristics about what makes a "good" change to a dependency
graph:</p>

<ul>
  <li>Small graphs are better than big ones. Avoid adding more nodes
    and edges than required by the problem.</li>
  <li>Edges are worse than nodes. Feel free to add more nodes if it allows
    you to remove some edges.</li>
  <li>Short dependency chains are better than long ones. Sometimes it is
    worth increasing code size a little bit to avoid depending on a node
    which itself has a chain of dependencies.</li>
</ul>

The baseline dependency graph should come from the domain that the program
is for. Thus, there is a limit to how far the size and complexity of the
graph can be reduced. All the variability comes from the impedence mismatch
between the theory of the domain and the concrete implementation on
physical hardware within the required efficiency constraints. The goal is
to minimize the extra nodes and edges added to the graph due to this
mismatch.

<h2>Interfaces are not modularity.</h2>

<p>I'm an advocate for strong and statically-checked interfaces between
modules. However, interfaces are orthogonal to the view I'm expressing
here. Splitting a single module into two modules does not make the program
more modular. Rather, it merely exposes the dependency structure between
the two parts. The interface is both documentation of the existing
structure and also a barrier against that structure being violated, but it
does not add structure by itself.</p>

<h2>Namespaces and files are not modularity.</h2>

<p>Namespaces and files are just boxes. You can use them to categorize
functions and types and all sorts of things, but they don't add structure.
Like interfaces, they can be used to document some existing structure, but
even for that use case they are faulty because there are no static checks
that everything has been put in the right box.</p>

<p>I have felt for a long time that namespace and files are overused. As an
observer, it seems to me that people use them to get that warm and fuzzy
"neat desk" feeling. Nice file structure might help them navigate a code
base better or avoid having an overwhelming amount of context present on
their screen. I have these feelings myself, but every time I indulge them,
the result has felt like a waste of time and effort.</p>

<p>Some concessions I'll make are that sometimes tools interact poorly with
certain namespace and file structures. For instance, really big files often
mean longer compile times and slower editor and IDE tools; my code files
rarely get to the point of slowing down my editor, but commands like
"type-at-cursor" and "go-to-definition" can get noticeably slower. These
sorts of concerns are pragmatic and language-dependent, and they do not
hurt my overall point.</p>

<?php essay_end() ?>
