<?php

/* index.twig */
class __TwigTemplate_96a17953c3dd74d3490811737b10e5b94de234c59deb74941dab42d63d364653 extends Twig_Template
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

";
        // line 3
        if ( !(null === (isset($context["user"]) ? $context["user"] : $this->getContext($context, "user")))) {
            // line 4
            echo "    <a href=\"#\">Logout</a> <br />
";
        }
        // line 6
        echo "
";
        // line 7
        if ((null === $this->getAttribute((isset($context["user"]) ? $context["user"] : $this->getContext($context, "user")), "meetupDetails", array()))) {
            // line 8
            echo "    Click the Meetup.com logo below to connect your account. <br />
    <a href=\"";
            // line 9
            echo twig_escape_filter($this->env, (isset($context["meetupAuthUrl"]) ? $context["meetupAuthUrl"] : $this->getContext($context, "meetupAuthUrl")), "html", null, true);
            echo "\">
        <img src=\"http://img2.meetupstatic.com/img/8308650022681532654/header/logo-2x.png\" />
    </a>
";
        } else {
            // line 13
            echo "    ";
            if ((null === $this->getAttribute((isset($context["user"]) ? $context["user"] : $this->getContext($context, "user")), "googleDetails", array()))) {
                // line 14
                echo "        <a href=\"";
                echo twig_escape_filter($this->env, (isset($context["googleAuthUrl"]) ? $context["googleAuthUrl"] : $this->getContext($context, "googleAuthUrl")), "html", null, true);
                echo "\" class=\"zocial google\">Sign in with Google</a>
    ";
            } else {
                // line 16
                echo "        Sign in
    ";
            }
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
        return array (  53 => 16,  47 => 14,  44 => 13,  37 => 9,  34 => 8,  32 => 7,  29 => 6,  25 => 4,  23 => 3,  19 => 1,);
    }
}
