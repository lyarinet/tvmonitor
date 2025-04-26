#!/bin/bash

# Colors and Styles
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
CYAN='\033[0;36m'
WHITE='\033[1;37m'
BOLD='\033[1m'
UNDERLINE='\033[4m'
NC='\033[0m' # No Color

# Extended Animation characters
SPINNER=('⣾' '⣽' '⣻' '⢿' '⡿' '⣟' '⣯' '⣷')
BAR_CHARS=(' ' '▏' '▎' '▍' '▌' '▋' '▊' '▉' '█')

# Function to display header with subtle gradient effect
header() {
    clear
    echo -e "${PURPLE}"
    echo "╔════════════════════════════════════════════════════════════╗"
    echo "║               ${WHITE}🚀 WEB DIRECTORY BACKUP TOOL ${PURPLE}              ║"
    echo "╚════════════════════════════════════════════════════════════╝"
    echo -e "${NC}"
    
    # Show system information
    echo -e "${CYAN}${BOLD}System:${NC} $(lsb_release -d | cut -f2-) | ${CYAN}${BOLD}Host:${NC} $(hostname)"
    echo -e "${CYAN}${BOLD}Date:${NC} $(date +"%Y-%m-%d %H:%M:%S") | ${CYAN}${BOLD}User:${NC} $(whoami)"
    echo ""
}

# Function to format seconds into human-readable time
format_time() {
    local seconds=$1
    if ((seconds < 60)); then
        printf "%2ds" $seconds
    elif ((seconds < 3600)); then
        printf "%2dm %2ds" $((seconds/60)) $((seconds%60))
    else
        printf "%2dh %2dm" $((seconds/3600)) $(( (seconds%3600)/60 ))
    fi
}

# Function to display multi-layer progress visualization
show_progress() {
    local current=$1
    local total=$2
    local start_time=$3
    local files_processed=$4
    local last_update=$5
    
    # Calculate metrics
    local percent=$((current * 10000 / total)) # Scale for 2 decimal places
    local elapsed=$(( $(date +%s) - start_time ))
    local elapsed_str=$(format_time $elapsed)
    
    # Calculate processing speed (files/sec)
    local speed=0
    local time_diff=$((elapsed - last_update))
    if (( time_diff > 0 )); then
        speed=$((files_processed / time_diff))
    fi
    
    # Calculate remaining time
    local remaining="--:--"
    if (( current > 0 && elapsed > 0 )); then
        remaining=$(( (total - current) * elapsed / current ))
        remaining=$(format_time $remaining)
    fi
    
    # Get terminal width
    local cols=$(tput cols)
    
    # ──────────────────── Progress Bar 1: Compact Overview ────────────────────
    local bar1_width=$((cols - 38))
    local bar1_progress=$((bar1_width * current / total))
    local bar1_remain=$((bar1_width - bar1_progress))
    
    # Fine-grained progress using sub-character blocks
    local fine_progress=$((bar1_width * current * 8 / total))
    local fine_chars=$((fine_progress / 8))
    local fine_remain=$((bar1_width - fine_chars))
    local fine_part=$((fine_progress % 8))
    
    # ──────────────────── Progress Bar 2: Speed Visualization ────────────────────
    local speed_width=10
    local speed_level=$((speed > 1000 ? 9 : speed / 100))
    ((speed_level = speed_level > 9 ? 9 : speed_level))
    local speed_bar=$(printf "%${speed_level}s" | tr ' ' '▓')
    local speed_remain=$(printf "%$((speed_width - speed_level))s")
    
    # Build the progress line
    printf "\r${SPINNER[elapsed % 8]} "
    printf "${BOLD}${WHITE}%3d.%.2d%%${NC} " $((percent/100)) $((percent%100))
    
    # Fine-grained progress bar
    printf "${BLUE}│${NC}"
    printf "%${fine_chars}s" | tr ' ' '█'
    if ((fine_remain > 0)); then
        printf "${BAR_CHARS[fine_part]}"
        printf "%${fine_remain}s"
    fi
    printf "${BLUE}│${NC} "
    
    # Speed indicator
    printf "${CYAN}${speed} files/s${NC} "
    printf "[${GREEN}${speed_bar}${CYAN}${speed_remain}]${NC} "
    
    # Time indicators
    printf "⌛${elapsed_str} ➔ ${remaining}"
    
    # Ensure we fill the line (important for smaller terminals)
    printf "%$((cols - $(printf "\r$0" | wc -m)))s"
}

# Function to validate directory with more detailed checks
validate_directory() {
    if [ ! -d "$1" ]; then
        echo -e "${RED}✗ Error: Directory '$1' does not exist!${NC}"
        return 1
    fi
    
    if [ ! -r "$1" ]; then
        echo -e "${RED}✗ Error: No read permission for directory '$1'!${NC}"
        return 1
    fi
    
    if [ -z "$(ls -A "$1" 2>/dev/null)" ]; then
        echo -e "${YELLOW}⚠ Warning: Directory '$1' is empty!${NC}"
        read -p "Continue anyway? (y/n): " confirm
        [[ "$confirm" != "y" ]] && return 1
    fi
    
    return 0
}

# Enhanced backup function with detailed statistics
create_backup() {
    local source_dir=$1
    local dest_dir=$2
    local description=$3
    
    # Get current date and time
    local timestamp=$(date +"%Y%m%d_%H%M%S")
    local hostname=$(hostname)
    local backup_file="${dest_dir}/web_backup_${hostname}_${timestamp}.tar.gz"
    
    # Count total files and calculate total size
    echo -e "${YELLOW}🔍 Analyzing source directory...${NC}"
    local total_files=$(find "$source_dir" -type f | wc -l)
    local total_size=$(du -sh "$source_dir" | cut -f1)
    local start_time=$(date +%s)
    local last_update=$start_time
    local processed_files=0
    
    echo -e "\n${WHITE}${BOLD}📦 Backup Summary:${NC}"
    echo -e "${BLUE}├─ Source:      ${WHITE}${source_dir}${NC}"
    echo -e "${BLUE}├─ Destination: ${WHITE}${backup_file}${NC}"
    echo -e "${BLUE}├─ Description: ${WHITE}${description}${NC}"
    echo -e "${BLUE}├─ Files:       ${WHITE}${total_files}${NC}"
    echo -e "${BLUE}└─ Size:        ${WHITE}${total_size}${NC}\n"
    
    # Start backup process
    echo -e "${PURPLE}${BOLD}🚀 Starting backup process...${NC}"
    
    # Create tar archive with detailed progress
    (find "$source_dir" -type f -print0 | 
        tar -czf "$backup_file" --null -T - --totals=USR1 2>&1) | 
    while IFS= read -r line; do
        if [[ $line == *"files"* ]]; then
            local new_files=$(echo $line | awk '{print $1}')
            processed_files=$((processed_files + new_files))
            local current_time=$(date +%s)
            
            show_progress "$processed_files" "$total_files" "$start_time" "$new_files" "$last_update"
            
            last_update=$current_time
        fi
    done
    
    # Final progress update
    show_progress "$total_files" "$total_files" "$start_time" "0" "$last_update"
    
    # Check backup result
    if [ $? -eq 0 ]; then
        local end_time=$(date +%s)
        local duration=$((end_time - start_time))
        local backup_size=$(du -h "$backup_file" | cut -f1)
        
        echo -e "\n\n${GREEN}${BOLD}✅ Backup completed successfully!${NC}"
        echo -e "${WHITE}┌───────────────────────────────┐"
        echo -e "│ ${BOLD}🏁 Backup Statistics${NC}         │"
        echo -e "├───────────────────────────────┤"
        echo -e "│ ${CYAN}● Duration:${NC} $(format_time $duration)  │"
        echo -e "│ ${CYAN}● Files:${NC} $total_files          │"
        echo -e "│ ${CYAN}● Size:${NC} $backup_size           │"
        echo -e "│ ${CYAN}● Speed:${NC} $((total_files / (duration > 0 ? duration : 1))) files/s │"
        echo -e "└───────────────────────────────┘\n"
        
        # Create detailed backup report
        create_backup_report "$backup_file" "$dest_dir" "$description" "$start_time" "$end_time" "$total_files" "$total_size"
    else
        echo -e "\n${RED}${BOLD}❌ Backup failed!${NC}"
        return 1
    fi
    
    return 0
}

# Function to create comprehensive backup report
create_backup_report() {
    local backup_file=$1
    local dest_dir=$2
    local description=$3
    local start_time=$4
    local end_time=$5
    local total_files=$6
    local total_size=$7
    
    local timestamp=$(date +"%Y%m%d_%H%M%S")
    local report_file="${dest_dir}/backup_report_${timestamp}.md"
    
    {
        echo "# 📁 Backup Report"
        echo "## 🔹 Basic Information"
        echo "- **Date**: $(date -d @$start_time +"%Y-%m-%d %H:%M:%S")"
        echo "- **Host**: $(hostname)"
        echo "- **User**: $(whoami)"
        echo ""
        echo "## 🔹 Backup Details"
        echo "- **Source Directory**: \`${source_dir}\`"
        echo "- **Backup File**: \`$(basename "$backup_file")\`"
        echo "- **Backup Size**: $total_size → $(du -h "$backup_file" | cut -f1) (compressed)"
        echo "- **Total Files**: $total_files"
        echo "- **Duration**: $(format_time $((end_time - start_time)))"
        echo ""
        echo "## 📝 Description"
        echo "$description"
        echo ""
        echo "## 📊 Statistics"
        echo "- **Compression Ratio**: $((100 - $(du -s "$backup_file" | awk '{print $1}') * 100 / $(du -s "$source_dir" | awk '{print $1}') ))%"
        echo "- **Average Speed**: $((total_files / ( (end_time - start_time) > 0 ? (end_time - start_time) : 1 ))) files/second"
        echo ""
        echo "## 🔍 Integrity Check"
        echo "To verify backup integrity, run:"
        echo "\`\`\`bash"
        echo "tar -tzf \"$backup_file\" | wc -l # Should return $total_files"
        echo "\`\`\`"
    } > "$report_file"
    
    echo -e "${CYAN}📄 Detailed report saved to: ${UNDERLINE}${report_file}${NC}"
}

# Main function with enhanced input validation
main() {
    header
    
    # Input source directory with path completion hint
    echo -e "${YELLOW}${BOLD}Please enter the web directory to backup:${NC}"
    echo -e "${BLUE}(Tip: Use Tab for path completion)${NC}"
    read -e -p "➤ Source path: " source_dir
    source_dir=$(eval echo "$source_dir") # Expand ~ and variables
    
    validate_directory "$source_dir" || exit 1
    
    # Input destination directory with free space check
    echo -e "\n${YELLOW}${BOLD}Please enter the backup destination directory:${NC}"
    read -e -p "➤ Destination path: " dest_dir
    dest_dir=$(eval echo "$dest_dir")
    
    mkdir -p "$dest_dir" || {
        echo -e "${RED}✗ Failed to create destination directory!${NC}"
        exit 1
    }
    
    # Check available space
    local source_size=$(du -s "$source_dir" | awk '{print $1}')
    local dest_space=$(df "$dest_dir" | awk 'NR==2 {print $4}')
    
    if (( source_size > dest_space )); then
        echo -e "${RED}✗ Not enough space! Need ~$((source_size/1024))MB but only $((dest_space/1024))MB available.${NC}"
        exit 1
    fi
    
    # Input description with multi-line support
    echo -e "\n${YELLOW}${BOLD}Please enter a description for this backup (Ctrl+D when done):${NC}"
    echo -e "${BLUE}(You can enter multiple lines)${NC}"
    description=$(cat)
    
    # Final confirmation with summary
    echo -e "\n${PURPLE}╔════════════════════════════════════════════════════════════╗"
    echo -e "║                 ${WHITE}${BOLD}📋 BACKUP SUMMARY${NC}${PURPLE}                 ║"
    echo -e "╠════════════════════════════════════════════════════════════╣"
    echo -e "║ ${CYAN}● Source:${NC} ${WHITE}${source_dir}${NC}"
    echo -e "║ ${CYAN}● Destination:${NC} ${WHITE}${dest_dir}${NC}"
    echo -e "║ ${CYAN}● Estimated Size:${NC} ${WHITE}$((source_size/1024)) MB${NC}"
    echo -e "║ ${CYAN}● Files:${NC} ${WHITE}$(find "$source_dir" -type f | wc -l)${NC}"
    echo -e "╚════════════════════════════════════════════════════════════╝${NC}"
    
    read -p "Start backup? (y/n): " confirm
    if [[ "$confirm" != "y" && "$confirm" != "Y" ]]; then
        echo -e "${RED}✗ Backup cancelled.${NC}"
        exit 0
    fi
    
    create_backup "$source_dir" "$dest_dir" "$description"
}

# Run main function
main