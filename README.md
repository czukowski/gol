Game of Life, of sorts
======================

A simple simulation, not unlike you may have seen in a [Darwinia intro][darwinia],
this one here is written in PHP and has a different set of rules.

To run, use the following command:

```sh
php bin/index.php
```

The initial world seed used by default is hardcoded (for now) in `bin/index.php`.

Rules
-----

Consider a representation of a "world" as an n by n matrix. Each element in the matrix may
contain 1 organism. Each organism lives, dies and reproduces according to the following set
of rules:

 * If there are two or three organisms of the same type living in the elements surrounding an
   organism of the same, type then it may survive.
 * If there are less than two organisms of one type surrounding one of the same type then it will
   die due to isolation.
 * If there are four or more organisms of one type surrounding one of the same type then it will
   die due to overcrowding.
 * If there are exactly three organisms of one type surrounding one element, they may give birth
   into that cell. The new organism is the same type as its parents. If this condition is true for
   more than one species on the same element then species type for the new element is chosen
   randomly.
 * If two organisms occupy one element, one of them must die (chosen randomly) (only to resolve
   initial conflicts).

The "world" and initial distribution of organisms within it is defined by an XML file of the
following format:

```xml
<?xml version="1.0" encoding="UTF­8"?>
<life>
    <world>
        <!-- Dimension of the square "world" -->
        <cells>n</cells>
        <!-- Number of distinct species -->
        <species>m</species>
        <!-- Number of iterations to be calculated -->
        <iterations>4000000</iterations>
    </world>
    <organisms>
        <organism>
            <!-- x position -->
            <x_pos>x</x_pos>
            <!-- y position -->
            <y_pos>y</y_pos>
            <!-- Species type -->
            <species>t</species>
        <organism>
    </organisms>
</life>
```

After iterations, the state of the "world" is to be saved in an XML file, `out.xml`, of the same
format as the initial definition file.

 [darwinia]: https://youtu.be/RIqPWw0sqOI
