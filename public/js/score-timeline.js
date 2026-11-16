(() => {
    const root = document.getElementById('scoreTimeline');
    if (!root) return;

    const series = JSON.parse(root.dataset.series);
    const allAwards = series.flatMap(person => person.awards);
    if (!allAwards.length) {
        root.querySelector('[data-empty]').hidden = false;
        return;
    }
    root.querySelector('[data-chart-content]').hidden = false;

    const svg = root.querySelector('[data-chart]');
    const legend = root.querySelector('[data-legend]');
    const plot = root.querySelector('[data-plot]');
    const tooltip = root.querySelector('[data-tooltip]');
    const chartSeries = [];
    let selectedTime = null;
    const colors = ['#b88624', '#73849c', '#bb6944', '#269aa0', '#8b78ce', '#b76491', '#719342', '#438cc4', '#b47730', '#84878c'];
    const timestamps = allAwards.map(award => Date.parse(award.time));
    const start = Math.min(...timestamps) - 60000;
    const end = Math.max(...timestamps) + 60000;
    const highest = Math.max(1, ...series.map(person => person.awards.reduce((sum, award) => sum + award.points, 0)));
    const interval = Math.max(1, 10 ** Math.floor(Math.log10(highest / 4)));
    const step = Math.ceil(highest / 4 / interval) * interval;
    const maximum = step * 4;
    const x = time => 60 + (time - start) / (end - start) * 870;
    const y = points => 280 - points / maximum * 240;
    const timeLabel = time => new Date(time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    const fullTime = time => new Date(time).toLocaleString();
    const number = value => value.toLocaleString(undefined, { maximumFractionDigits: 2 });

    function draw(tag, attributes, parent = svg, text) {
        const node = document.createElementNS('http://www.w3.org/2000/svg', tag);
        Object.entries(attributes).forEach(([key, value]) => node.setAttribute(key, value));
        if (text !== undefined) node.textContent = text;
        parent.append(node);
        return node;
    }

    draw('text', { x: 60, y: 18, fill: 'currentColor', 'font-size': 12 }, svg, 'Points');
    for (let i = 0; i <= 4; i++) {
        const points = step * i;
        draw('line', { x1: 60, x2: 930, y1: y(points), y2: y(points), stroke: 'currentColor', opacity: .12 });
        draw('text', { x: 48, y: y(points) + 4, 'text-anchor': 'end', fill: 'currentColor', 'font-size': 12 }, svg, number(points));
    }
    for (let i = 0; i <= 5; i++) {
        const time = start + (end - start) * i / 5;
        draw('text', { x: x(time), y: 305, 'text-anchor': i === 0 ? 'start' : i === 5 ? 'end' : 'middle', fill: 'currentColor', 'font-size': 12 }, svg, timeLabel(time));
    }

    series.forEach((person, index) => {
        const color = colors[index] ?? `hsl(${(index * 137.508) % 360} 55% 45%)`;
        const group = draw('g', { 'aria-label': person.name });
        let total = 0;
        let previousX = 60;
        let previousY = y(0);
        let path = `M ${previousX} ${previousY}`;
        const points = person.awards.map(award => {
            total += award.points;
            const time = Date.parse(award.time);
            const nextX = x(time);
            const nextY = y(total);
            const middleX = (previousX + nextX) / 2;
            // Horizontal tangents keep the curve smooth without overshooting either score.
            path += ` C ${middleX} ${previousY}, ${middleX} ${nextY}, ${nextX} ${nextY}`;
            previousX = nextX;
            previousY = nextY;
            const description = `${person.name}: +${number(award.points)} points for ${award.challenge}. Total ${number(total)}. ${fullTime(time)} (${award.source}).`;
            return { time, total, description };
        });
        path += ' H 930';
        const line = draw('path', { d: path, fill: 'none', stroke: color, 'stroke-width': 2 }, group);
        const entry = { name: person.name, index, color, group, line, points, visible: true };
        chartSeries.push(entry);
        points.forEach(point => {
            const dot = draw('circle', { cx: x(point.time), cy: y(point.total), r: 4, fill: color, tabindex: 0, role: 'img', 'aria-label': point.description }, group);
            draw('title', {}, dot, point.description);
            dot.addEventListener('focus', () => showAt(point.time));
        });

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn btn-sm btn-ghost';
        button.setAttribute('aria-pressed', 'true');
        const swatch = document.createElement('span');
        swatch.setAttribute('aria-hidden', 'true');
        swatch.style.cssText = `width:18px;border-top:3px solid ${color}`;
        button.append(swatch, document.createTextNode(person.name));
        button.addEventListener('click', () => {
            const visible = button.getAttribute('aria-pressed') !== 'true';
            button.setAttribute('aria-pressed', String(visible));
            button.style.opacity = visible ? '1' : '.5';
            group.style.display = visible ? '' : 'none';
            entry.visible = visible;
            if (selectedTime !== null) showAt(selectedTime);
        });
        legend.append(button);
    });

    const guide = draw('line', { x1: 60, x2: 60, y1: 40, y2: 280, stroke: 'currentColor', opacity: .35, visibility: 'hidden', 'pointer-events': 'none' });

    function showAt(time, pointerY) {
        selectedTime = time;
        const scores = chartSeries.filter(person => person.visible).map(person => {
            let total = 0;
            for (const point of person.points) {
                if (point.time > time) break;
                total = point.total;
            }
            return { ...person, total };
        }).sort((a, b) => b.total - a.total || a.index - b.index);
        const nearest = pointerY === undefined ? null : scores.reduce((best, person) =>
            !best || Math.abs(y(person.total) - pointerY) < Math.abs(y(best.total) - pointerY) ? person : best, null);

        tooltip.replaceChildren();
        const heading = document.createElement('div');
        heading.style.cssText = 'font-weight:700;margin-bottom:8px;font-variant-numeric:tabular-nums';
        heading.textContent = new Date(time).toLocaleTimeString();
        tooltip.append(heading);
        scores.forEach(person => {
            const row = document.createElement('div');
            row.style.cssText = `display:flex;align-items:center;gap:8px;padding:3px 0;font-weight:${person === nearest ? 700 : 400}`;
            const swatch = document.createElement('span');
            swatch.style.cssText = `width:12px;flex:none;border-top:3px solid ${person.color}`;
            swatch.setAttribute('aria-hidden', 'true');
            const name = document.createElement('span');
            name.style.cssText = 'flex:1;min-width:0;overflow-wrap:anywhere';
            name.textContent = person.name;
            const points = document.createElement('span');
            points.style.fontVariantNumeric = 'tabular-nums';
            points.textContent = number(person.total);
            row.append(swatch, name, points);
            tooltip.append(row);
        });
        if (!scores.length) tooltip.append(document.createTextNode('No competitors selected.'));
        chartSeries.forEach(person => {
            person.line.setAttribute('stroke-width', person.index === nearest?.index ? 3.5 : 2);
            person.group.style.opacity = nearest && person.index !== nearest.index ? '.45' : '1';
        });

        const chartX = x(time);
        guide.setAttribute('x1', chartX);
        guide.setAttribute('x2', chartX);
        guide.setAttribute('visibility', 'visible');
        tooltip.hidden = false;
        const bounds = plot.getBoundingClientRect();
        const point = svg.createSVGPoint();
        point.x = chartX;
        point.y = 40;
        const position = point.matrixTransform(svg.getScreenCTM());
        const cursorX = position.x - bounds.left;
        const width = tooltip.offsetWidth;
        const left = cursorX + width + 16 > bounds.width ? cursorX - width - 16 : cursorX + 16;
        tooltip.style.left = `${Math.max(8, Math.min(left, bounds.width - width - 8))}px`;
        tooltip.style.top = `${Math.max(8, Math.min(position.y - bounds.top, bounds.height - tooltip.offsetHeight - 8))}px`;
    }

    function hideTooltip() {
        selectedTime = null;
        tooltip.hidden = true;
        guide.setAttribute('visibility', 'hidden');
        chartSeries.forEach(person => {
            person.line.setAttribute('stroke-width', 2);
            person.group.style.opacity = '1';
        });
    }

    svg.addEventListener('pointermove', event => {
        const point = svg.createSVGPoint();
        point.x = event.clientX;
        point.y = event.clientY;
        const cursor = point.matrixTransform(svg.getScreenCTM().inverse());
        if (cursor.x < 60 || cursor.x > 930 || cursor.y < 40 || cursor.y > 280) {
            hideTooltip();
            return;
        }
        showAt(start + (cursor.x - 60) / 870 * (end - start), cursor.y);
    });
    plot.addEventListener('pointerleave', hideTooltip);
    window.addEventListener('beforeprint', hideTooltip);
    svg.addEventListener('focus', () => showAt(end));
    svg.addEventListener('focusout', event => {
        if (!svg.contains(event.relatedTarget)) hideTooltip();
    });
    svg.addEventListener('keydown', event => {
        if (event.key === 'Escape') {
            hideTooltip();
            return;
        }
        if (!['ArrowLeft', 'ArrowRight'].includes(event.key)) return;
        event.preventDefault();
        const times = [...new Set([start, ...timestamps, end])].sort((a, b) => a - b);
        const current = selectedTime ?? end;
        const time = event.key === 'ArrowRight'
            ? times.find(value => value > current) ?? end
            : times.reverse().find(value => value < current) ?? start;
        showAt(time);
    });
})();
