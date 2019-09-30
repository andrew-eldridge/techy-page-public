let body = document.getElementsByTagName("body")[0];
let wrapper = document.getElementById("wrapper");
let banner = document.getElementById("banner");
let logoWrapper = document.getElementById("logowrapper");
let titleWrapper = document.getElementById("titlewrapper");
let accountWrapper = document.getElementById("accountwrapper");
let accountDropdown = document.getElementById("accountdropdown");
let dropdownContent = document.getElementsByClassName("dropdowncontent");
let main = document.getElementsByTagName("main")[0];
let antiShadow = document.getElementById("antishadow");
let shadow = document.getElementById("shadow");
let nav = document.getElementById("testnav");
let navsections = document.getElementsByClassName("navsection");

let activeNavSection = -1;

let accountDropdownActive = false;

let stylesheet = document.getElementById("stylesheet");
let pageWidth = window.innerWidth;
let pageHeight = window.innerHeight;

window.addEventListener("hashchange", updateUI);

$(document).ready(function(){
    let placeHolder = ["Search a language", "Search a location", "Search a company", "Search a name", "Search a skill"];
    let n=0;
    let loopLength=placeHolder.length;
    let searchbaritem = document.getElementById('searchbaritem');

    setInterval(function(){
       if(n<loopLength){
          let newPlaceholder = placeHolder[n];
          n++;
          $(searchbaritem).attr('placeholder',newPlaceholder);
       } else {
          $(searchbaritem).attr('placeholder',placeHolder[0]);
          n=0;
       }
    }, 3000);
});

function updateUI() {

	pageWidth = window.innerWidth;
	pageHeight = window.innerHeight;

	let titleWidth = pageWidth - logoWrapper.offsetWidth - 110;
	let mainHeight = pageHeight - banner.offsetHeight;
	titleWrapper.style.width = titleWidth + "px";
	main.style.height = mainHeight + "px";

	let credentials = document.getElementsByClassName("credentials");
	let imageContainers = document.getElementsByClassName("imagecontainer");

	// Main will span the entire screen in mobile view
	if (pageWidth > 920) {
		let mainWidth = pageWidth - 100;
		main.style.marginLeft = "100px";
		main.style.width = mainWidth + "px";
		if (pageWidth > 1420) {
			Array.prototype.forEach.call(credentials, function(item) {
				item.style.width = "600px";
			});
		} else {
			Array.prototype.forEach.call(credentials, function(item) {
				item.style.width = "300px";
			});
		}
	} else {
		main.style.marginLeft = "0px";
		main.style.width = pageWidth + "px";
		Array.prototype.forEach.call(credentials, function(item) {
			item.style.width = "auto";
			/* TODO: possibly need to implement reactive sizing for credentials div
			let credItemWidth = pageWidth - 60 - imageContainers[i].offsetWidth;
			item.style.width = credItemWidth + "px";

			 */
		});
	}

	wrapper.style.display = "block";

	updateNavHeight();

	if (activeNavSection != -1) {
		updateDropdownHeight();
	}

	if (document.body.contains(document.getElementsByClassName("message-banner")[0])) {
		setTimeout(function() {
			let messageBanner = document.getElementsByClassName("message-banner")[0];
			messageBanner.style.height = "0px";
			messageBanner.style.padding = "0px";
			setTimeout(function() {
				messageBanner.style.display = "none";
			}, 100);
		}, 4000);
	}

}

function displayForm(id) {
	let targetForm = document.getElementById(id);
	targetForm.style.display = "flex";
}

function getRandomColor() {

	let runes = "0123456789ABCDEF";
	let color = "#";
	for (i = 0; i < 6; i++) {
		color += runes[Math.floor(Math.random() * 16)]
	}

	return color

}

function setBackgroundColor() {
	let color = getRandomColor();
    wrapper.style.backgroundColor = color;
}

function toggleDropdown(section) {

	if (section != -1) {
		
		if (dropdownContent[section].style.display == "block") {
			dropdownContent[section].style.display = "none";
			antiShadow.style.display = "none";
			activeNavSection = -1;
			navsections[section].style.backgroundColor = "#eee";
		} else {
			for (i = 0; i < dropdownContent.length; i++) {
				dropdownContent[i].style.display = "none";
				navsections[i].style.backgroundColor = "#eee";
			}
			dropdownContent[section].style.display = "block";
			antiShadow.style.display = "block";
			navsections[section].style.backgroundColor = "gold";
			activeNavSection = section;
			updateDropdownHeight();
		}
		
	} else {
		
		if (accountDropdown.style.display == "block") {
			accountDropdown.style.display = "none";
			accountWrapper.style.backgroundColor = "#fff";
			accountDropdownActive = false;
		} else {
			accountDropdown.style.display = "block";
			accountWrapper.style.backgroundColor = "gold";
			accountDropdownActive = true;
		}
		
	}

}

function updateDropdownHeight() {
    let mainHeight = main.offsetHeight;
    for (i = 0; i < dropdownContent.length; i++) {
		dropdownContent[i].style.height = mainHeight + "px";
    }
    shadow.style.height = mainHeight + "px";
}

function updateNavHeight() {
	let mainHeight = main.offsetHeight;
	let navHeight = mainHeight;
	nav.style.height = navHeight + "px";
}

function updateNav(section, hover) {
	if (activeNavSection == -1 || activeNavSection != section) {
		if (section != -2) {
            if (hover) {
                navsections[section].style.backgroundColor = "lavender";
            } else {
                navsections[section].style.backgroundColor = "#eee";
            }
		} else if (!accountDropdownActive) {
            if (hover) {
                accountWrapper.style.backgroundColor = "lavender";
            } else {
                accountWrapper.style.backgroundColor = "#fff";
            }
		}
	}
}

function showWageOptions() {
	let checkbox = document.getElementById("freelancer");
	let wageoptions = document.getElementById("wageoptions");
	if (checkbox.checked) {
		wageoptions.style.display = "block";
	} else {
		wageoptions.style.display = "none";
	}
}

function showSocialInput(site) {
	let checkbox = document.getElementById(site);
	let linkedininput = document.getElementById("linkedininput");
	let facebookinput = document.getElementById("facebookinput");
	let twitterinput = document.getElementById("twitterinput");
	let submitinput = document.getElementById("submitinput");
	if (site == "linkedin") {
		if (checkbox.checked == true) {
			linkedininput.style.display = "block";
			submitinput.style.display = "block";
		} else {
			linkedininput.style.display = "none";
			if (facebookinput.style.display == "none" && twitterinput.style.display == "none") {
				submitinput.style.display = "none";
			}
		};
	} else if (site == "facebook") {
		if (checkbox.checked == true) {
			facebookinput.style.display = "block";
			submitinput.style.display = "block";
		} else {
			facebookinput.style.display = "none";
			if (linkedininput.style.display == "none" && twitterinput.style.display == "none") {
				submitinput.style.display = "none";
			}
		};
	} else if (site == "twitter") {
		if (checkbox.checked == true) {
			twitterinput.style.display = "block";
			submitinput.style.display = "block";
		} else {
			twitterinput.style.display = "none";
			if (facebookinput.style.display == "none" && linkedininput.style.display == "none") {
				submitinput.style.display = "none";
			}
		};
	};
}

function incompleteMessage() {
	alert("This feature is unavailable in the beta release. Please check back later or contact support at support@thelotus.network if you have any questions.");
}

function redirect(page) {
	window.location.href = page;
}
