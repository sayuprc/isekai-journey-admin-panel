const fromOn = document.getElementById('from_on') as HTMLInputElement
const toOn = document.getElementById('to_on') as HTMLInputElement

const copyTodayBtn = document.getElementById('copy_today_btn') as HTMLButtonElement
const copyFromToBtn = document.getElementById('copy_from_to_btn') as HTMLButtonElement

const copyToday = (): void => {
  const today = new Date()
  fromOn.value = `${today.getFullYear()}-${(('0' + (today.getMonth() + 1))).slice(-2)}-${('0' + today.getDate()).slice(-2)}`
}

const copyFromTo = (): void => {
  toOn.value = fromOn.value
}

copyTodayBtn.addEventListener('click', copyToday)
copyFromToBtn.addEventListener('click', copyFromTo)

type LinkTypes = LinkType[]

interface LinkType {
  id: string
  name: string
}

const linkTypes = (window.linkTypes) as LinkTypes

const addLinkBtn = document.getElementById('add_link_btn') as HTMLButtonElement
const links = document.getElementById('links') as HTMLDivElement

const createInput = (id: string, textContent: string, type: string, value: string = ''): HTMLDivElement => {
  const div = document.createElement('div')
  div.className = 'form-group'

  const label = document.createElement('label')
  label.htmlFor = id
  label.textContent = textContent

  const groupDiv = document.createElement('div')
  groupDiv.className = 'input-group'

  const input = document.createElement('input')
  input.id = id
  input.name = id
  input.className = `form-control`
  input.type = type
  input.value = value

  groupDiv.appendChild(input)

  div.appendChild(label)
  div.appendChild(groupDiv)

  return div
}

const createSelect = (id: string, textContent: string, data: LinkTypes, selected?: string): HTMLDivElement => {
  const div = document.createElement('div')
  div.className = 'form-group'

  const label = document.createElement('label')
  label.htmlFor = id
  label.textContent = textContent

  const groupDiv = document.createElement('div')
  groupDiv.className = 'input-group'

  const select = document.createElement('select')
  select.id = id
  select.name = id
  select.className = `form-control`

  data.forEach((item) => {
    const option = document.createElement('option')
    option.value = item.id
    option.textContent = item.name
    if (selected === item.id) {
      option.selected = true
    }
    select.appendChild(option)
  })

  groupDiv.appendChild(select)
  div.appendChild(label)
  div.appendChild(groupDiv)

  return div
}

const addLink = (): void => {
  const count = links.querySelectorAll('.link').length

  const div = document.createElement('div')
  div.className = 'link form-group'

  div.appendChild(createInput(`links[${count}][link_name]`, `リンク名${count + 1}`, `text`))
  div.appendChild(createInput(`links[${count}][url]`, `リンクURL${count + 1}`, `text`))
  div.appendChild(createInput<number>(`links[${count}][order_no]`, `リンク表示順${count + 1}`, `number`, 0))
  div.appendChild(createSelect(`links[${count}][link_type_id]`, `リンク種別${count + 1}`, linkTypes))

  links.appendChild(div)
}

addLinkBtn.addEventListener('click', addLink)

type OldLinks = OldLink[]

interface OldLink {
  link_name: string
  link_type_id: string
  order_no: number
  url: string
}

const oldLinks = (window.oldLinks) as OldLinks

// あれば復元
oldLinks?.forEach((oldLink) => {
  const count = links.querySelectorAll('.link').length

  const div = document.createElement('div')
  div.className = 'link form-group'

  div.appendChild(createInput(`links[${count}][link_name]`, `リンク名${count + 1}`, `text`, oldLink.link_name))
  div.appendChild(createInput(`links[${count}][url]`, `リンクURL${count + 1}`, `text`, oldLink.url))
  div.appendChild(createInput(`links[${count}][order_no]`, `リンク表示順${count + 1}`, `number`, `${oldLink.order_no}`))
  div.appendChild(createSelect(`links[${count}][link_type_id]`, `リンク種別${count + 1}`, linkTypes, oldLink.link_type_id))

  links.appendChild(div)
})
