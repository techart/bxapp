let activeTab = 0
const debugBar = document.querySelector(".tba_debug_bar")
const prefix = debugBar.dataset.prefix
const tabs = debugBar.querySelectorAll(".tba_debug_bar__tab")
const content = debugBar.querySelectorAll(".tba_debug_bar__body-content")
const body = debugBar.querySelector(".tba_debug_bar__body")
const deleteCacheButtons = debugBar.querySelectorAll(".tba_debug_bar__button.deleteBtn")
const deleteAllCacheButton = debugBar.querySelector('#cacheClearAll')
const resizeTarget = debugBar.querySelector('.tba_debug_bar__resizeLine')

let heightBar = localStorage.getItem('TbaDebugBarHeight') ?? 500

function toggleBody(tab) {
	body.classList.toggle("show")
	if (body.classList.contains("show")) {
		tabs[tab].click()
		body.style.height = `${heightBar}px`;
	} else {
		body.style.height = `0px`;
	}
}

function closeDebugbar() {
	debugBar.classList.toggle("hide")
}

tabs.forEach((tab, index) => {
	tab.addEventListener("click", () => {
		tabs[activeTab].classList.remove("active")
		content[activeTab].classList.remove("vis")
		tab.classList.add("active")
		content[index].classList.add("vis")
		activeTab = index
	})
})

deleteCacheButtons.forEach(btn => {
	btn.addEventListener('click', () => {
		const link = btn.dataset.link
		
		if (link) {
			fetch('/' + prefix + '/bxappdefault/cache/delete/', { 
				'method': 'POST',
				headers: {
					'Content-Type': 'application/json',
				},
				'body': JSON.stringify({
					'cacheLink': [link]
				})
			})
			.then(response => response.json())
			.then(data => {
				if (data.result.data.links) {
					btn.parentNode.classList.add('removed')
					btn.remove() 
				}
			})
			.catch(() => alert('Не удалось удалить кеш'))
		}
	})
})

deleteAllCacheButton.addEventListener('click', () => {
	if (confirm('Точно хотите удалить весь кеш?')) {
		let links = []
		deleteCacheButtons.forEach(btn => {
			links.push(btn.dataset.link)
		})

		fetch('/' + prefix + '/bxappdefault/cache/delete/', { 
			'method': 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			'body': JSON.stringify({
				'cacheLink': links
			})
		})
		.then(response => response.json())
		.then(data => {
			const links = data.result.data.links

			deleteCacheButtons.forEach(btn => {
				if (links.indexOf(btn.dataset.link) !== -1) {
					btn.parentNode.classList.add('removed')
					btn.remove()
				}
			})
		})
	}
})

let pos = heightBar
let resizeBar = false

const resizeDebugBar = (event) => {
	heightBar = Number(heightBar) + Number(pos) - Number(event.clientY)
	pos = event.clientY
	body.style.height = `${heightBar}px` 
	localStorage.setItem('TbaDebugBarHeight', heightBar)
}

resizeTarget.addEventListener('mousedown', (event) => {
	if (body.classList.contains('show')) {
		pos = event.clientY
		resizeBar = true

		document.addEventListener('mousemove', resizeDebugBar) 
	}
})

document.addEventListener('mouseup', () => {
	if (body.classList.contains('show') && resizeBar) {
		document.removeEventListener('mousemove', resizeDebugBar)
		resizeBar = false
	}
})

resizeTarget.addEventListener('mouseover', () => {
	if (body.classList.contains('show')) {
		resizeTarget.classList.add('resizable')
	}
})

resizeTarget.addEventListener('mouseout', () => {
	if (body.classList.contains('show')) {
		resizeTarget.classList.remove('resizable')
	}
})