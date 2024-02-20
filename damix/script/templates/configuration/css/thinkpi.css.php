<?php
/**
* @package      damix
* @Module       engines
* @author       PANIEN Vincent
* @copyright    2023 LGPL-3.0 license
*/?>* {
  box-sizing: border-box;
}

.header {
  text-align: right;
}
.menu {
  float: left;
  width: 200px;
  text-align: left;
}

.menu a {
  background-color: #e5e5e5;
  padding: 8px;
  margin-top: 7px;
  display: block;
  width: 100%;
  color: black;
}

.main {
  float: left;
  width: 80%;
}

.right {
  background-color: #e5e5e5;
  float: left;
  width: 10%;
  padding: 15px;
  margin-top: 7px;
  text-align: center;
}



/* formulaire */
.form-required{
	color: #ff0000;
}

/* Style the tab */
.tab {
  
  border: 1px solid #ccc;
  background-color: #f1f1f1;
}

/* Style the buttons inside the tab */
.tab button {
  background-color: inherit;
  border: none;
  outline: none;
  cursor: pointer;
  padding: 14px 16px;
  transition: 0.3s;
  font-size: 17px;
}

/* Change background color of buttons on hover */
.tab button:hover {
  background-color: #ddd;
}

/* Create an active/current tablink class */
.tab button.active {
  background-color: #e31937;
  color: #fff;
  font-weight: bold;
}

.dataTables_wrapper .dataTable {
	width: 100% !important;
}


/* boutons */

.btn {
	display: inline-block;
	user-select: none;
	border: 1px solid transparent;
	padding: 0.65rem 1rem;
	border-radius: 0.25rem;
}
.btn i {
    padding-right: 0.5rem;
    vertical-align: middle;
    line-height: 0; 
}

.damix-dt_btn-action {
    margin: 3px;
    font-weight: bold;
    font-size: 10px;
}

.damix-dt_btn-action-small {
	padding: 4px 4px 4px 0px !important;
	font-size: 18px;
}
 
.btn-damix-vert1 {
    background-color: #2abb9b;
    border-color: #2abb9b;
    color: #ffffff;
}

.btn-damix-vert1:hover {
    background-color: #28a789;
    border-color: #28a789;
    color: #ffffff;
}

.btn-damix-vert1:focus, .btn-damix-vert1.focus {
    -webkit-box-shadow: 0 0 0 0.2rem rgba(42, 187, 155, 0.5);
    box-shadow: 0 0 0 0.2rem rgba(42, 187, 155, 0.5);
}