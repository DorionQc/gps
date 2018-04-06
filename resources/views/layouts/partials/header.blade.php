<header>
	<div class="top-bar-container" data-sticky-container>
		<div class="sticky sticky-topbar" data-sticky
			data-options="anchor: page; marginTop: 0; stickyOn: small;">
			<div class="title-bar" data-responsive-toggle="example-menu"
				data-hide-for="medium">
				<button class="menu-icon" type="button" data-toggle="example-menu"></button>
				<div class="title-bar-title">Menu</div>
			</div>
			<div class="top-bar" id="example-menu">
				<div class="top-bar-left">
					<ul class="menu" data-responsive-menu="accordion medium">
						<li class="menu-text">GPS</li>
						@if(Auth::check() && Auth::user()->isAdmin)
						<li class="<?php if (Route::getCurrentRoute()->getName() == 'controllerHome') echo 'is-active' ?>">
							<a href="/controller/home">Home</a>
						</li>
						<li class="<?php if (Route::getCurrentRoute()->getName() == 'makeTruck') echo 'is-active' ?>">
							<a href="/controller/trucks">Vehicles</a>
						</li>
						<li class="<?php if (Route::getCurrentRoute()->getName() == 'makeItem') echo 'is-active' ?>">
							<a href="/controller/items">Items</a>
						</li>
						<li class="<?php if (Route::getCurrentRoute()->getName() == 'makeClient') echo 'is-active' ?>">
							<a href="/controller/clients">Clients</a>
						</li>
						<li class="<?php if (Route::getCurrentRoute()->getName() == 'makeSupplier') echo 'is-active' ?>">
							<a href="/controller/suppliers">Suppliers</a>
						</li>
						<li class="<?php if (Route::getCurrentRoute()->getName() == 'makeCommand') echo 'is-active' ?>">
							<a href="/controller/commands">Commands</a>
						</li>
						<li class="<?php if (Route::getCurrentRoute()->getName() == 'makeSession') echo 'is-active' ?>">
							<a href="/controller/sessions">Sessions</a>
						</li>
						<li class="<?php if (Route::getCurrentRoute()->getName() == 'pay') echo 'is-active' ?>">
							<a href="/controller/pay">Pay</a>
						</li>
						@endif
					</ul>
				
				</div>
				<div class="top-bar-right">
					@if (Auth::check())
    					<ul class="dropdown menu"
    						data-responsive-menu="accordion medium-dropdown">
    						<li class="has-submenu"><a href="/truck/session">{{ Auth::user()->name }}</a>
    							<ul class="submenu menu vertical" data-submenu>
    								<li><a href="/truck/session">Camionneur</a></li>
    								@if(Auth::user()->isAdmin)
    									<li><a href="/controller/home">Centre de contrôle</a></li>
    								@endif
    
    								<li class="nav-item dropdown">
    									<div class="dropdown-menu" aria-labelledby="navbarDropdown">
    										<a class="dropdown-item" href="{{ route('logout') }}"
    											onclick="event.preventDefault();
                                                         document.getElementById('logout-form').submit();">
    											Logout </a>
    
    										<form id="logout-form" action="{{ route('logout') }}"
    											method="POST" style="display: none;">@csrf</form>
    									</div>
    								</li>
    							</ul>
    						</li> 
    					</ul>
    				@else
    					<ul class="menu"
    						data-responsive-menu="accordion medium">
        					<li class="<?php if (Route::getCurrentRoute()->getName() == 'login') echo 'is-active' ?>">
        						<a href="{{ route('login') }}">Login</a>
        					</li>
        					<li class="<?php if (Route::getCurrentRoute()->getName() == 'register') echo 'is-active' ?>">
        						<a href="{{ route('register') }}">Register</a>
        					</li>
        				</ul>
    				@endif
				</div>
			</div>
		</div>
	</div>
</header>
