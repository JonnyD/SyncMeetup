<?php

/* index.twig */
class __TwigTemplate_9595b8c5b9dde0fe6c5779425e4285e81bb943552ed86073c20512798ee34a14 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        // line 1
        echo "<h1>Sync Meetup</h1>

Click the Meetup.com logo below to connect your account. <br />
";
        // line 4
        if ((null === $this->getAttribute((isset($context["user"]) ? $context["user"] : $this->getContext($context, "user")), "meetupId", array()))) {
            // line 5
            echo "    <a href=\"";
            echo twig_escape_filter($this->env, (isset($context["meetupAuthUrl"]) ? $context["meetupAuthUrl"] : $this->getContext($context, "meetupAuthUrl")), "html", null, true);
            echo "\">
        <img src=\"http://img2.meetupstatic.com/img/8308650022681532654/header/logo-2x.png\" />
    </a>
";
        }
    }

    public function getTemplateName()
    {
        return "index.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  26 => 5,  24 => 4,  19 => 1,);
    }
}