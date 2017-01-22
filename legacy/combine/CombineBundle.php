<?php

namespace Polonairs\Dialtime\CombineBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Polonairs\Dialtime\CombineBundle\DependencyInjection\CombineExtension;

class CombineBundle extends Bundle
{
	public function getContainerExtension()
	{
		return new CombineExtension();
	}
}
