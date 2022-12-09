<?php

namespace GeekBrains\PHPUnit\Container;

class ClassDependingOnAnother
{
	public function __construct(
		private SomeClassWithoutDependencies $one,
		private SomeClassWithParameter $two
	) {
	}

}